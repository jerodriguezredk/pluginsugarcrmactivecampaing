# Diagrama de Flujo de Sincronización / Sync Flow Diagram

## Arquitectura General / General Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                           SugarCRM                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │  Contacts    │  │   Accounts   │  │    Leads     │              │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘              │
│         │                  │                  │                      │
│         └──────────────────┴──────────────────┘                      │
│                            │                                         │
│                  ┌─────────▼──────────┐                             │
│                  │  Logic Hooks       │                             │
│                  │  - before_save     │                             │
│                  │  - after_save      │                             │
│                  │  - after_delete    │                             │
│                  └─────────┬──────────┘                             │
│                            │                                         │
│         ┌──────────────────┴──────────────────┐                     │
│         │                                      │                     │
│  ┌──────▼────────┐                    ┌───────▼─────────┐          │
│  │  Schedulers   │                    │ ActiveCampaign  │          │
│  │  - Every 15min│                    │     Class       │          │
│  │  - Batch: 100 │                    │  API Connector  │          │
│  └───────────────┘                    └───────┬─────────┘          │
│                                               │                      │
└───────────────────────────────────────────────┼──────────────────────┘
                                                │
                        ┌───────────────────────▼───────────────────────┐
                        │         ActiveCampaign API                     │
                        │  - POST /api/3/contact/sync                   │
                        │  - GET  /api/3/contacts                       │
                        │  - POST /api/3/webhooks                       │
                        └───────────────────────┬───────────────────────┘
                                                │
┌───────────────────────────────────────────────┼──────────────────────┐
│                      ActiveCampaign           │                       │
│                                               │                       │
│  ┌─────────────────────────────────────┐     │                       │
│  │        Contact Lists                │◄────┘                       │
│  │  - List 1 ← SugarCRM Target List 1  │                            │
│  │  - List 2 ← SugarCRM Target List 2  │                            │
│  └─────────────────────────────────────┘                            │
│                     │                                                 │
│  ┌──────────────────▼──────────────────┐                            │
│  │      Campaign Activities             │                            │
│  │  - Email Sent                        │                            │
│  │  - Email Opened                      │                            │
│  │  - Link Clicked                      │                            │
│  └──────────────────┬──────────────────┘                            │
│                     │                                                 │
│                     │ Webhook                                         │
└─────────────────────┼─────────────────────────────────────────────────┘
                      │
                      │ POST /rest/v11_4/TL_ActiveCampaigns/CampaignStats
                      │
┌─────────────────────▼─────────────────────────────────────────────────┐
│                  SugarCRM - REST API Endpoint                          │
│                                                                        │
│  ┌──────────────────────────────────────────────────────┐            │
│  │  TL_ActiveCampaignsApi::GetCampaignStats()          │            │
│  │  1. Receive webhook from ActiveCampaign              │            │
│  │  2. Find CRM contact using CRM_RECORD_ID             │            │
│  │  3. Create TL_ActiveCampaigns record                 │            │
│  │  4. Link to Contact/Account/Lead                     │            │
│  └──────────────────────────────────────────────────────┘            │
│                            │                                           │
│                            ▼                                           │
│  ┌────────────────────────────────────────────┐                      │
│  │  TL_ActiveCampaigns Module (Subpanels)     │                      │
│  │  - Campaign Name                            │                      │
│  │  - Activity Date                            │                      │
│  │  - Status (sent/opened/clicked)             │                      │
│  └────────────────────────────────────────────┘                      │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Flujo de Sincronización de Datos / Data Sync Flow

### 1. Nuevo Contacto en SugarCRM / New Contact in SugarCRM

```
Usuario crea Contact en SugarCRM
    │
    ├─ Tiene email válido?
    │   ├─ NO  → ⚠️ No se sincroniza
    │   └─ SI  → Continuar
    │
    ├─ Pertenece a Target List mapeada?
    │   ├─ NO  → Requiere scheduler o mapeo global
    │   └─ SI  → Continuar
    │
    ├─ "Sync New" activado?
    │   ├─ NO  → ⚠️ No se sincroniza
    │   └─ SI  → Continuar
    │
    └─► Logic Hook (after_save)
            │
            ├─ Preparar datos mapeados
            │
            └─► API Call: POST /api/3/contact/sync
                    │
                    ├─ Éxito → Guardar active_campaign_id_c
                    └─ Error → Log error
```

### 2. Actualización de Contacto / Contact Update

```
Usuario actualiza Contact en SugarCRM
    │
    ├─ Han pasado > 5 minutos desde última actualización?
    │   ├─ NO  → ⚠️ Actualización ignorada (throttling)
    │   └─ SI  → Continuar
    │
    ├─ Tiene active_campaign_id_c?
    │   ├─ NO  → Tratar como nuevo contacto
    │   └─ SI  → Continuar
    │
    └─► Logic Hook (after_save)
            │
            └─► API Call: POST /api/3/contact/sync
                    │
                    └─ ActiveCampaign actualiza o crea contacto
```

### 3. Campaña en ActiveCampaign / Campaign in ActiveCampaign

```
Se envía campaña desde ActiveCampaign
    │
    ├─► Webhook: Email Sent
    │      │
    │      └─► SugarCRM: CampaignStats
    │             │
    │             └─ Crear registro TL_ActiveCampaigns
    │                Status: "sent"
    │
    ├─► Contacto abre email
    │      │
    │      └─► Webhook: Email Opened
    │             │
    │             └─► SugarCRM: CampaignStats
    │                    │
    │                    └─ Actualizar o crear TL_ActiveCampaigns
    │                       Status: "opened"
    │
    └─► Contacto hace clic en link
           │
           └─► Webhook: Link Clicked
                  │
                  └─► SugarCRM: CampaignStats
                         │
                         └─ Actualizar o crear TL_ActiveCampaigns
                            Status: "clicked"
```

### 4. Lead Scoring Sync / Sincronización de Puntuación

```
Scheduler ejecuta cada 15 minutos
    │
    └─► GetLeadScores Scheduler
            │
            ├─ API Call: GET /api/3/contacts?filters[updated_after]=...
            │
            ├─ Para cada contacto con scoreValue:
            │     │
            │     ├─ Buscar en SugarCRM por active_campaign_id_c
            │     │
            │     └─ Actualizar campo ac_lead_score_c
            │           │
            │           └─ Puede actualizar: Contacts, Accounts, Leads
            │
            └─ Procesa contactos actualizados en últimos 2 días
```

---

## Tabla de Módulos y Campos / Modules and Fields Table

| Módulo SugarCRM | Campo SugarCRM          | Campo ActiveCampaign | Dirección      | Obligatorio |
|-----------------|-------------------------|---------------------|----------------|-------------|
| Contacts        | email                   | email               | →              | ✓           |
| Contacts        | first_name              | first_name          | →              | ✓           |
| Contacts        | last_name               | last_name           | →              | ✓           |
| Contacts        | phone_mobile            | phone               | →              |             |
| Contacts        | active_campaign_id_c    | contact.id          | ←→             |             |
| Contacts        | ac_lead_score_c         | scoreValue          | ←              |             |
| Accounts        | email (from contact)    | email               | →              | ✓           |
| Accounts        | name                    | first_name          | →              | ✓           |
| Accounts        | phone_office            | phone               | →              |             |
| Accounts        | active_campaign_id_c    | contact.id          | ←→             |             |
| Accounts        | ac_lead_score_c         | scoreValue          | ←              |             |
| Leads           | email                   | email               | →              | ✓           |
| Leads           | first_name              | first_name          | →              | ✓           |
| Leads           | last_name               | last_name           | →              | ✓           |
| Leads           | phone_mobile            | phone               | →              |             |
| Leads           | active_campaign_id_c    | contact.id          | ←→             |             |
| Leads           | ac_lead_score_c         | scoreValue          | ←              |             |

**Leyenda / Legend:**
- `→` : SugarCRM a ActiveCampaign / SugarCRM to ActiveCampaign
- `←` : ActiveCampaign a SugarCRM / ActiveCampaign to SugarCRM
- `←→`: Bidireccional / Bidirectional

---

## Eventos y Triggers / Events and Triggers

### Logic Hooks

| Hook            | Módulo  | Función                    | Acción                          |
|-----------------|---------|----------------------------|----------------------------------|
| before_save     | Contact | save_contact               | Validar y preparar datos         |
| after_save      | Contact | link_to_active_campaign    | Crear/actualizar en AC          |
| after_delete    | Contact | delete_contact_from_ac     | Eliminar de AC                  |
| before_save     | Account | save_account               | Validar y preparar datos         |
| after_save      | Account | link_to_active_campaign    | Crear/actualizar en AC          |
| after_delete    | Account | delete_account_from_ac     | Eliminar de AC                  |
| before_save     | Lead    | save_lead                  | Validar y preparar datos         |
| after_save      | Lead    | link_to_active_campaign    | Crear/actualizar en AC          |
| after_delete    | Lead    | delete_lead_from_ac        | Eliminar de AC                  |

### Schedulers

| Scheduler                              | Función                    | Frecuencia  | Batch Size |
|----------------------------------------|----------------------------|-------------|------------|
| ActiveCampaigns - Sync Existing Contacts | CreateCampaignContact    | */15 min    | 300        |
| ActiveCampaigns - Sync Existing Accounts | CreateCampaignAccount    | */15 min    | 300        |
| ActiveCampaigns - Sync Existing Leads    | CreateCampaignLead       | */15 min    | 300        |
| ActiveCampaigns - Sync Existing Contracts| CreateCampaignContract   | */15 min    | 300        |
| ActiveCampaigns - Get Lead Scores        | GetLeadScores            | */15 min    | -          |
| ActiveCampaigns - Get Existing Lead Scores| GetExistingLeadScores   | */15 min    | -          |

### Webhooks

| Evento AC       | Endpoint SugarCRM              | Método | Acción                        |
|-----------------|--------------------------------|--------|-------------------------------|
| sent            | /TL_ActiveCampaigns/CampaignStats | POST | Crear registro con status "sent" |
| open            | /TL_ActiveCampaigns/CampaignStats | POST | Crear registro con status "opened" |
| click           | /TL_ActiveCampaigns/CampaignStats | POST | Crear registro con status "clicked" |

---

## Resumen de Limitaciones Clave / Key Limitations Summary

### ⚠️ Limitaciones Críticas / Critical Limitations

1. **Email obligatorio** - Sin email, no hay sincronización
2. **Throttling de 5 minutos** - Actualizaciones frecuentes se ignoran
3. **Sincronización unidireccional de datos básicos** - SugarCRM → AC principalmente
4. **Requiere Target Lists** - Para sincronización automática de nuevos registros
5. **URL pública necesaria** - Para recibir webhooks de ActiveCampaign

### ✅ Mitigaciones / Mitigations

1. **Schedulers para sincronización masiva** - Procesa registros existentes
2. **Logs detallados** - Para debugging y monitoreo
3. **Mapeo flexible** - Configuración personalizable de campos
4. **Batch processing** - Evita sobrecarga de API

---

**Última actualización / Last update:** 2026-02-18
