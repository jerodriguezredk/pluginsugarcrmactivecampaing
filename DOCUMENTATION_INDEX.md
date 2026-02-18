# Índice de Documentación / Documentation Index

Este repositorio contiene documentación completa del plugin ActiveCampaign para SugarCRM.

This repository contains comprehensive documentation for the ActiveCampaign plugin for SugarCRM.

---

## 📚 Archivos de Documentación / Documentation Files

### 1. [README.md](./README.md)
**Español/English**
- Resumen general del plugin
- Enlaces a documentación completa
- Instalación rápida

**General plugin overview**
- Links to complete documentation
- Quick installation guide

---

### 2. [DOCUMENTACION.md](./DOCUMENTACION.md) 🇪🇸
**Documentación Completa en Español**

Contenido:
- ✅ Descripción general del plugin
- ✅ Funcionalidades completas
- ✅ Información que sincroniza (campos, módulos)
- ✅ Dirección de sincronización (SugarCRM ↔ ActiveCampaign)
- ✅ 17 Limitaciones técnicas y funcionales documentadas
- ✅ Requisitos del sistema
- ✅ Guía de configuración paso a paso
- ✅ Instrucciones de uso
- ✅ Solución de problemas

**Páginas:** 458 líneas  
**Idioma:** Español

---

### 3. [DOCUMENTATION_EN.md](./DOCUMENTATION_EN.md) 🇬🇧
**Complete Documentation in English**

Contents:
- ✅ Plugin overview
- ✅ Complete feature list
- ✅ Synchronized data (fields, modules)
- ✅ Sync direction (SugarCRM ↔ ActiveCampaign)
- ✅ 17 Technical and functional limitations documented
- ✅ System requirements
- ✅ Step-by-step configuration guide
- ✅ Usage instructions
- ✅ Troubleshooting

**Pages:** 458 lines  
**Language:** English

---

### 4. [SYNC_FLOW_DIAGRAM.md](./SYNC_FLOW_DIAGRAM.md) 🌐
**Diagramas de Flujo y Referencia Rápida / Flow Diagrams and Quick Reference**

Contenido / Contents:
- ✅ Diagrama de arquitectura del sistema / System architecture diagram
- ✅ Flujos de sincronización visuales / Visual sync flows
- ✅ Tablas de mapeo de módulos y campos / Module and field mapping tables
- ✅ Eventos y triggers / Events and triggers
- ✅ Resumen de limitaciones / Limitations summary

**Formato / Format:** ASCII diagrams + tables  
**Idioma / Language:** Bilingüe / Bilingual

---

## 🎯 Guía de Uso / Usage Guide

### Para desarrolladores SugarCRM / For SugarCRM Developers
1. Leer [DOCUMENTACION.md](./DOCUMENTACION.md) o [DOCUMENTATION_EN.md](./DOCUMENTATION_EN.md)
2. Revisar [SYNC_FLOW_DIAGRAM.md](./SYNC_FLOW_DIAGRAM.md) para entender el flujo de datos

### Para administradores / For Administrators
1. Comenzar con [README.md](./README.md) para resumen
2. Seguir guía de configuración en documentación completa

### Para consulta rápida / For Quick Reference
- [SYNC_FLOW_DIAGRAM.md](./SYNC_FLOW_DIAGRAM.md) - Diagramas y tablas de referencia

---

## 📋 Información Cubierta / Covered Information

### ✅ Funcionalidades / Features
- Sincronización bidireccional de datos
- Webhooks para estadísticas de campaña
- Lead scoring automático
- Mapeo personalizable de campos
- Schedulers para sincronización masiva

### ✅ Sincronización de Información / Information Synchronization

#### Módulos Sincronizados / Synchronized Modules:
- Contactos (Contacts)
- Cuentas (Accounts)
- Leads
- Contratos (Contracts)
- Listas de Objetivos (Target Lists)

#### Direcciones de Sincronización / Sync Directions:
- **SugarCRM → ActiveCampaign:** Crear/Actualizar/Eliminar contactos
- **ActiveCampaign → SugarCRM:** Estadísticas de campaña, Lead scores

### ✅ Limitaciones Documentadas / Documented Limitations

**Técnicas / Technical:**
1. Requisito de email obligatorio
2. Throttling de actualizaciones (5 min)
3. Límites de schedulers (300 registros/batch)
4. Rate limiting de API
5. Requisito de URL pública para webhooks
6. Validación de licencia

**Funcionales / Functional:**
7. Sincronización unidireccional de datos básicos
8. Dependencia de Target Lists
9. Limitaciones de lead scoring
10. No sincronización de eliminaciones desde AC
11. Estadísticas solo lectura
12. Mapeo manual de campos
13. Detección de duplicados basada en email
14. Limitaciones de contratos
15. Procesamiento síncrono
16. Logging extensivo
17. Un solo registro de estadística por campaña

---

## 🔍 Búsqueda Rápida / Quick Search

### Necesitas saber... / Need to know...

**¿Qué sincroniza?** → [DOCUMENTACION.md#información-que-sincroniza](./DOCUMENTACION.md#información-que-sincroniza)

**¿En qué dirección?** → [DOCUMENTACION.md#dirección-de-sincronización](./DOCUMENTACION.md#dirección-de-sincronización)

**¿Qué limitaciones tiene?** → [DOCUMENTACION.md#limitaciones](./DOCUMENTACION.md#limitaciones)

**¿Cómo configurarlo?** → [DOCUMENTACION.md#configuración](./DOCUMENTACION.md#configuración)

**¿Cómo usarlo?** → [DOCUMENTACION.md#uso-del-plugin](./DOCUMENTACION.md#uso-del-plugin)

**¿Ver flujos de datos?** → [SYNC_FLOW_DIAGRAM.md](./SYNC_FLOW_DIAGRAM.md)

---

## 📊 Estadísticas de Documentación / Documentation Statistics

| Archivo / File              | Líneas / Lines | Tamaño / Size | Idioma / Language |
|----------------------------|----------------|---------------|-------------------|
| DOCUMENTACION.md           | 458            | 17 KB         | Español           |
| DOCUMENTATION_EN.md        | 458            | 15 KB         | English           |
| SYNC_FLOW_DIAGRAM.md       | 269            | 17 KB         | Bilingüe          |
| README.md                  | 60             | 2.4 KB        | Bilingüe          |
| **TOTAL**                  | **1,245**      | **51.4 KB**   | -                 |

---

## ✨ Características de la Documentación / Documentation Features

- ✅ Completamente bilingüe (Español/English)
- ✅ Diagramas visuales de flujo de datos
- ✅ Tablas de referencia rápida
- ✅ 17 limitaciones documentadas en detalle
- ✅ Guías paso a paso
- ✅ Ejemplos de uso
- ✅ Solución de problemas
- ✅ Referencias a documentación oficial

---

## 🆘 Soporte / Support

Para preguntas sobre el plugin, consultar primero la documentación completa.

For questions about the plugin, please consult the complete documentation first.

**Documentación de Referencia / Reference Documentation:**
- [SugarCRM Developer Guide](https://support.sugarcrm.com/Documentation/Sugar_Developer/Sugar_Developer_Guide_9.0)
- [ActiveCampaign API Documentation](https://www.activecampaign.com/api/overview.php)

---

**Última actualización / Last updated:** 2026-02-18  
**Versión del Plugin / Plugin Version:** 3.2  
**Mantenedor / Maintainer:** jerodriguezredk
