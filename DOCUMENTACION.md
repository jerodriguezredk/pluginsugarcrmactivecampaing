# Documentación del Plugin ActiveCampaign para SugarCRM

## Índice
1. [Descripción General](#descripción-general)
2. [Funcionalidades](#funcionalidades)
3. [Información que Sincroniza](#información-que-sincroniza)
4. [Dirección de Sincronización](#dirección-de-sincronización)
5. [Limitaciones](#limitaciones)
6. [Requisitos](#requisitos)
7. [Configuración](#configuración)
8. [Uso del Plugin](#uso-del-plugin)

---

## Descripción General

El plugin **ActiveCampaign para SugarCRM** permite la integración bidireccional entre SugarCRM y ActiveCampaign, facilitando la sincronización de datos de contactos, cuentas, leads y listas de objetivos (Target Lists) entre ambas plataformas, así como el seguimiento de estadísticas de campañas de email marketing.

**Versión:** 3.2  
**Compatibilidad:** SugarCRM 9.x  
**PHP requerido:** 7.3

---

## Funcionalidades

### 1. Sincronización de Datos
El plugin sincroniza los siguientes módulos de SugarCRM con ActiveCampaign:

- **Contactos (Contacts)**
- **Cuentas (Accounts)**
- **Leads**
- **Contratos (Contracts)**
- **Listas de Objetivos (Target Lists)**

### 2. Seguimiento de Estadísticas de Campaña
Mediante webhooks, el plugin captura y registra las siguientes actividades de campaña:

- **Enviado (Sent):** Cuando se envía un email de campaña
- **Abierto (Opened):** Cuando un contacto abre el email
- **Clic (Clicked):** Cuando un contacto hace clic en enlaces del email

### 3. Puntuación de Leads (Lead Scoring)
- Sincronización de puntuaciones de leads desde ActiveCampaign a SugarCRM
- Actualización automática del campo `ac_lead_score_c` en Contactos, Cuentas y Leads

### 4. Campos Personalizados
El plugin crea automáticamente campos personalizados en ActiveCampaign:
- **CRM_SOURCE:** Módulo de origen (Contacts, Accounts, Leads)
- **CRM_RECORD_ID:** ID del registro en SugarCRM

### 5. Mapeo de Campos Personalizable
- Interfaz de configuración para mapear campos de SugarCRM con campos de ActiveCampaign
- Soporte para campos personalizados en ambos sistemas

---

## Información que Sincroniza

### Campos Estándar Sincronizados

#### Contactos (Contacts)
- **Email** (campo obligatorio)
- **Nombre (first_name)**
- **Apellido (last_name)**
- **Teléfono móvil (phone_mobile)**
- **ID de ActiveCampaign (active_campaign_id_c)**
- **Estado de sincronización (sync_status_c)**
- **Puntuación de Lead (ac_lead_score_c)**
- **Campos personalizados mapeados**

#### Cuentas (Accounts)
- **Email** (campo obligatorio)
- **Nombre (name)**
- **Teléfono (phone_office)**
- **ID de ActiveCampaign (active_campaign_id_c)**
- **Estado de sincronización (sync_status_c)**
- **Puntuación de Lead (ac_lead_score_c)**
- **Campos personalizados mapeados**

#### Leads
- **Email** (campo obligatorio)
- **Nombre (first_name)**
- **Apellido (last_name)**
- **Teléfono móvil (phone_mobile)**
- **ID de ActiveCampaign (active_campaign_id_c)**
- **Estado de sincronización (sync_status_c)**
- **Puntuación de Lead (ac_lead_score_c)**
- **Campos personalizados mapeados**

#### Contratos (Contracts)
- **Email del contacto asociado**
- **Campos personalizados mapeados**

### Estadísticas de Campaña Sincronizadas
Las estadísticas se almacenan en el módulo **TL_ActiveCampaigns** con:
- **Nombre de la campaña**
- **Fecha de actividad**
- **ID de campaña de ActiveCampaign**
- **Estado (sent/opened/clicked)**
- **Relación con Contact/Account/Lead**

---

## Dirección de Sincronización

### De SugarCRM a ActiveCampaign

#### 1. Nuevos Registros
**Cuándo:** Al crear o guardar un nuevo registro en SugarCRM  
**Cómo:** Logic Hooks (after_save)  
**Condiciones:**
- La sincronización de nuevos registros debe estar activada en la configuración
- El registro debe tener un email válido (campo obligatorio)
- El registro debe estar asociado a una Lista de Objetivos mapeada con una lista de ActiveCampaign

**Módulos afectados:**
- Contacts → ActiveCampaign Contacts
- Accounts → ActiveCampaign Contacts
- Leads → ActiveCampaign Contacts
- Contracts → ActiveCampaign Contacts

#### 2. Actualización de Registros Existentes
**Cuándo:** Al modificar un registro existente en SugarCRM  
**Cómo:** Logic Hooks (before_save/after_save)  
**Condiciones:**
- Debe haber transcurrido al menos 5 minutos desde la última actualización
- El registro debe tener un `active_campaign_id_c` válido
- La sincronización debe estar activada

#### 3. Eliminación de Registros
**Cuándo:** Al eliminar un registro en SugarCRM  
**Cómo:** Logic Hooks (after_delete)  
**Resultado:** El contacto se elimina de ActiveCampaign

#### 4. Sincronización Masiva (Schedulers)
**Cuándo:** Mediante trabajos programados  
**Frecuencia:** Cada 15 minutos (configurable)  
**Funcionalidad:**
- Sincroniza registros existentes que aún no tienen `active_campaign_id_c`
- Procesa hasta 300 registros por ejecución (3 lotes de 100)

**Schedulers disponibles:**
- `CreateCampaignContact` - Sincroniza contactos existentes
- `CreateCampaignAccount` - Sincroniza cuentas existentes
- `CreateCampaignLead` - Sincroniza leads existentes
- `CreateCampaignContract` - Sincroniza contratos existentes
- `GetLeadScores` - Actualiza puntuaciones de leads
- `GetExistingLeadScores` - Actualiza puntuaciones históricas

### De ActiveCampaign a SugarCRM

#### 1. Webhooks para Estadísticas de Campaña
**Endpoint:** `/rest/v11_4/TL_ActiveCampaigns/CampaignStats`  
**Eventos capturados:**
- `sent` - Email enviado
- `open` - Email abierto
- `click` - Link clickeado

**Proceso:**
1. ActiveCampaign envía webhook cuando ocurre un evento
2. El plugin busca el contacto en SugarCRM usando `CRM_RECORD_ID`
3. Crea un registro en el módulo `TL_ActiveCampaigns`
4. Relaciona el registro con Contact/Account/Lead mediante subpanel

#### 2. Sincronización de Contactos (Opcional - Desactivado)
**Endpoints disponibles (no usados actualmente):**
- `/rest/v11_4/TL_ActiveCampaigns/SyncAcContacts` - Crear contactos
- `/rest/v11_4/TL_ActiveCampaigns/UpdateAcContacts` - Actualizar contactos

**Nota:** Estos endpoints están implementados pero los webhooks correspondientes no se crean automáticamente.

#### 3. Puntuación de Leads
**Cuándo:** Mediante scheduler configurado  
**Dirección:** ActiveCampaign → SugarCRM  
**Funcionalidad:**
- Obtiene contactos actualizados en los últimos 2 días
- Sincroniza el campo `scoreValue` al campo `ac_lead_score_c`
- Actualiza Contacts, Accounts y Leads

---

## Limitaciones

### Limitaciones Técnicas

#### 1. Requisito de Email
**Descripción:** Todos los registros deben tener una dirección de email válida para sincronizarse.  
**Impacto:** Registros sin email no pueden sincronizarse a ActiveCampaign.  
**Módulos afectados:** Todos (Contacts, Accounts, Leads, Contracts)

#### 2. Sincronización Unidireccional de Datos Básicos
**Descripción:** La sincronización de datos maestros (nombre, apellido, teléfono) es principalmente de SugarCRM a ActiveCampaign.  
**Impacto:** Cambios en estos campos en ActiveCampaign no se reflejan automáticamente en SugarCRM (excepto mediante los endpoints no configurados).

#### 3. Throttling de Actualizaciones
**Descripción:** Las actualizaciones de un mismo registro solo se procesan si han pasado más de 5 minutos desde la última modificación.  
**Impacto:** Cambios rápidos sucesivos no se sincronizan todos.  
**Razón:** Evitar sobrecarga de API y actualizaciones duplicadas.

#### 4. Limitaciones de Schedulers
**Descripción:** 
- Procesan máximo 300 registros por ejecución (3 lotes de 100)
- Ejecutan cada 15 minutos por defecto
  
**Impacto:** La sincronización masiva de grandes volúmenes de datos puede tardar varias horas.

#### 5. Dependencia de Target Lists para Sincronización Automática
**Descripción:** Para que un nuevo registro se sincronice automáticamente, debe estar asociado a una Target List mapeada con una lista de ActiveCampaign.  
**Impacto:** Registros creados directamente sin Target List requieren:
- Mapeo global de listas de ActiveCampaign para el módulo, O
- Sincronización posterior mediante scheduler

#### 6. Lead Scoring - Sincronización Limitada
**Descripción:** 
- Solo sincroniza contactos actualizados en últimos 2 días
- El scheduler de puntuaciones históricas se desactiva automáticamente después de 50 ejecuciones sin datos
  
**Impacto:** Puntuaciones antiguas pueden no sincronizarse completamente.

### Limitaciones de API de ActiveCampaign

#### 7. Rate Limiting
**Descripción:** ActiveCampaign impone límites de tasa en su API.  
**Impacto:** Sincronizaciones masivas muy grandes pueden fallar o ralentizarse.  
**Mitigación:** Los schedulers procesan en lotes pequeños (100 registros).

#### 8. Webhooks Requieren URL Pública
**Descripción:** Los webhooks de ActiveCampaign necesitan una URL accesible públicamente.  
**Impacto:** No funciona en entornos localhost sin túnel (ej: ngrok).  
**Solución:** El plugin detecta URLs de ngrok y localhost y ajusta la URL del webhook.

### Limitaciones de Licencia

#### 9. Validación de Licencia Requerida
**Descripción:** El plugin requiere una licencia válida de SugarActive Outfitters.  
**Impacto:** Sin licencia válida, las funcionalidades no se ejecutan.  
**Validación:** Se valida en cada operación importante (sync, webhooks, config).

### Limitaciones Funcionales

#### 10. No Sincroniza Eliminaciones desde ActiveCampaign
**Descripción:** Si se elimina un contacto en ActiveCampaign, no se elimina automáticamente en SugarCRM.  
**Dirección:** Solo SugarCRM → ActiveCampaign para eliminaciones.

#### 11. Estadísticas de Campaña Solo Lectura
**Descripción:** Las estadísticas creadas en el módulo TL_ActiveCampaigns no se pueden editar manualmente de forma significativa.  
**Impacto:** Son solo para visualización y reporte.

#### 12. Un Solo Registro de Estadística por Campaña
**Descripción:** Si ya existe una estadística para una campaña específica y un contacto, se actualiza el estado en lugar de crear un nuevo registro.  
**Impacto:** No hay historial de múltiples interacciones con la misma campaña.

#### 13. Mapeo Manual de Campos
**Descripción:** Los campos personalizados deben mapearse manualmente en la interfaz de configuración.  
**Impacto:** Requiere configuración inicial y mantenimiento cuando se añaden nuevos campos.

#### 14. Duplicados Basados en Email
**Descripción:** La detección de duplicados se basa principalmente en el email.  
**Impacto:** Contactos con emails diferentes pero datos similares pueden duplicarse.

#### 15. Sincronización de Contratos Limitada
**Descripción:** Los contratos sincronizan usando el email del contacto asociado, no datos propios del contrato.  
**Impacto:** La funcionalidad para contratos es limitada comparada con otros módulos.

### Consideraciones de Rendimiento

#### 16. Procesamiento Síncrono en Logic Hooks
**Descripción:** Los Logic Hooks ejecutan llamadas a API de forma síncrona.  
**Impacto:** 
- Guardar un registro puede tardar más (espera respuesta de ActiveCampaign)
- Si ActiveCampaign está lento, afecta la experiencia del usuario

#### 17. Logging Extensivo
**Descripción:** El plugin genera logs detallados de todas las operaciones.  
**Impacto:** 
- Los archivos de log pueden crecer rápidamente
- Útil para debugging pero requiere limpieza periódica
  
**Mitigación:** Interfaz para descargar y limpiar logs.

---

## Requisitos

### Requisitos Técnicos
- **SugarCRM:** Versión 9.x
- **PHP:** Versión 7.3
- **Base de Datos:** MySQL/MariaDB con permisos para crear tablas
- **Servidor Web:** Accesible públicamente (para webhooks)

### Requisitos de ActiveCampaign
- **Cuenta de ActiveCampaign** activa
- **API Key** con permisos completos
- **API URL** de la cuenta

### Requisitos de Licencia
- **Licencia válida** de SugarActive Outfitters para TL_ActiveCampaigns

---

## Configuración

### 1. Instalación
1. Acceder al panel de administración de SugarCRM
2. Ir a **Module Loader**
3. Cargar el archivo ZIP del plugin
4. Ejecutar la instalación
5. Realizar **Repair & Rebuild** desde Admin → Repair

### 2. Configuración Inicial

#### Paso 1: Autenticación con ActiveCampaign
1. Navegar a **TL_ActiveCampaigns** → **Config**
2. Ingresar:
   - **API URL:** URL de la API de ActiveCampaign (ej: `https://TUACCOUNT.api-us1.com`)
   - **API Key:** Clave de API de ActiveCampaign
3. Hacer clic en **Check API** para verificar la conexión
4. Guardar configuración

**Nota:** Al guardar, el plugin:
- Crea tabla `tl_migrated_stats` para tracking
- Crea webhooks en ActiveCampaign automáticamente
- Crea campos personalizados en ActiveCampaign (CRM_SOURCE, CRM_RECORD_ID)

#### Paso 2: Mapear Listas
1. En la página de configuración, seleccionar:
   - **Target List de SugarCRM**
   - **Lista(s) de ActiveCampaign** correspondiente(s)
2. Activar opciones de sincronización:
   - **Sync Existing:** Sincronizar registros existentes
   - **Sync New:** Sincronizar nuevos registros automáticamente
3. Guardar configuración

#### Paso 3: Mapear Campos
1. Navegar a **TL_ActiveCampaigns** → **Mapping**
2. Para cada módulo (Contacts, Accounts, Leads, Contracts):
   - Seleccionar campo de SugarCRM
   - Seleccionar campo correspondiente de ActiveCampaign
   - Añadir a mapeo
3. Guardar mapeo

**Campos obligatorios:**
- Email (siempre requerido)
- First Name
- Last Name

#### Paso 4: Activar Schedulers (Opcional)
1. Ir a **Admin** → **Schedulers**
2. Buscar schedulers de ActiveCampaign:
   - ActiveCampaigns - Sync Existing Contacts
   - ActiveCampaigns - Sync Existing Accounts
   - ActiveCampaigns - Sync Existing Leads
   - ActiveCampaigns - Sync Existing Contracts
   - ActiveCampaigns - Get Lead Scores
3. Configurar frecuencia (default: cada 15 minutos)
4. Activar según necesidad

---

## Uso del Plugin

### Sincronizar Nuevos Registros
1. Crear o editar un Contact/Account/Lead
2. Añadir a una Target List mapeada con ActiveCampaign
3. El registro se sincroniza automáticamente si "Sync New" está activado
4. Verificar campo `active_campaign_id_c` se llena con ID de ActiveCampaign

### Sincronizar Registros Existentes
**Opción 1: Manual mediante Target Lists**
1. Añadir registros a una Target List mapeada
2. Asegurar que "Sync Existing" está activado para esa lista
3. El scheduler sincronizará los registros

**Opción 2: Scheduler Automático**
1. Activar el scheduler correspondiente al módulo
2. El scheduler procesará registros sin `active_campaign_id_c`
3. Máximo 300 registros por ejecución

### Ver Estadísticas de Campaña
1. Abrir un Contact/Account/Lead
2. Ir al subpanel **ActiveCampaigns**
3. Ver estadísticas de campañas:
   - Nombre de campaña
   - Fecha de actividad
   - Estado (Sent/Opened/Clicked)

### Verificar Logs
1. Navegar a **TL_ActiveCampaigns** → **Logs**
2. Revisar operaciones y errores
3. Descargar o limpiar logs según necesidad

### Monitorear Puntuaciones de Leads
1. Los campos `ac_lead_score_c` se actualizan automáticamente
2. Disponibles en:
   - DetailView de Contact/Account/Lead
   - ListView (si se añade a layout)
   - Reportes y dashboards

---

## Solución de Problemas Comunes

### Los registros no se sincronizan
**Verificar:**
- [ ] Email es válido y no vacío
- [ ] Licencia es válida
- [ ] API Key y URL son correctos
- [ ] Target List está mapeada
- [ ] "Sync New" o "Sync Existing" está activado
- [ ] Revisar logs para errores específicos

### Webhooks no reciben datos
**Verificar:**
- [ ] URL del servidor es accesible públicamente
- [ ] Webhook fue creado en ActiveCampaign (revisar en AC)
- [ ] Firewall no bloquea requests de ActiveCampaign
- [ ] URL contiene protocolo correcto (https preferido)

### Schedulers no ejecutan
**Verificar:**
- [ ] Schedulers están en estado "Active"
- [ ] Cron de SugarCRM está corriendo
- [ ] No hay errores de licencia
- [ ] Revisar SugarCRM scheduler logs

### Actualizaciones no se reflejan
**Verificar:**
- [ ] Han pasado más de 5 minutos desde última actualización
- [ ] Campo `active_campaign_id_c` tiene un valor válido
- [ ] Sincronización está activada en configuración

---

## Soporte y Contacto

Para asistencia con instalación o uso del plugin, contactar al proveedor del plugin.

**Documentación de Referencia:**
- [SugarCRM Developer Guide](https://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_9.0)
- [ActiveCampaign API Documentation](https://www.activecampaign.com/api/overview.php)

---

## Changelog

### Versión 3.2
- Sincronización bidireccional de Contacts, Accounts, Leads, Contracts
- Webhooks para estadísticas de campaña (sent/opened/clicked)
- Lead scoring automático
- Mapeo personalizable de campos
- Schedulers para sincronización masiva
- Interfaz de configuración mejorada
- Sistema de logging detallado

---

**Última actualización:** 2026-02-18
