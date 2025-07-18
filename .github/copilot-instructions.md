# 🤖 Copilot Instructions for POS System
## 🏗️ Project Architecture
- **Backend:** Laravel (PHP) in `app/` (Controllers, Services, Models, Traits)
  - **Controllers** (`app/Http/Controllers/`): Orquestan la lógica de flujo, validan requests y delegan a servicios. Usan Inertia.js para renderizar vistas Vue o retornan JSON para APIs.
  - **Services** (`app/Services/`): Encapsulan la lógica de negocio y acceso a datos. Cada entidad principal (Producto, Cliente, Categoría, Factura, Sesión POS, Configuración, Widget) tiene su propio servicio con métodos CRUD, filtrado avanzado y lógica específica.
  - **Patrón de servicios:**
    - Métodos comunes: `filterAndPaginate`, `paginate`, `all`, `find`, `create`, `update`, `delete`.
    - Servicios de negocio (ej: `InvoiceService`, `PosSessionService`) implementan validaciones de reglas, transacciones y lógica de dominio.
    - Servicios de widgets (`DashboardWidgetService`) gestionan la configuración, posición, filtros y datos de cada widget.
- **Frontend:** Vue 3 + TypeScript en `resources/js/` (pages, components, UI)
  - Usa Composition API, componentes modulares y utilidades CSS tipo Tailwind.
  - Comunicación con backend vía Inertia.js y endpoints REST.
- **Dashboard Widgets:** Modular, documentado en `docs/WIDGETS_DOCUMENTATION.md` y `docs/WIDGETS_SUMMARY.md`. Widgets configurables, drag & drop, y filtrables.
- **Data Flow:**
  - Los controladores reciben requests, validan y delegan a servicios.
  - Los servicios devuelven datos estructurados (paginados, colecciones, arrays) para la vista o API.
  - Inertia.js conecta controladores con páginas Vue.
  - Los widgets obtienen datos a través de servicios especializados y exponen opciones de filtrado avanzadas.
  - Ejemplo: `ProductController@index` → `ProductService@filterAndPaginate` → Eloquent → Vue.
## ⚡ Developer Workflows
- **Build:** `npm run build` (VS Code task "Build POS System")
- **Dev:** `npm run dev` (hot reload frontend)
- **Test (PHP):** `php artisan test` o `vendor/bin/phpunit` (tests en `tests/`)
- **Test (JS):** No hay suite estándar, agregar según necesidad
- **Debug:** Laravel logs (`storage/logs/`), devtools de navegador, overlays de errores Vue
- **Migrations/Seeders:** En `database/` (`php artisan migrate`, `php artisan db:seed`)
- **Endpoints y flujos:**
  - Los controladores siguen el patrón resourceful (index, show, create, store, edit, update, destroy)
  - Los servicios implementan lógica de filtrado, paginación y validación avanzada
  - Ejemplo de flujo: `POST /products` → `ProductController@store` → `ProductService@create`
## 🧩 Project-Specific Conventions
- **Widget System:**
  - Widgets definidos y documentados en `docs/WIDGETS_DOCUMENTATION.md`.
  - UI y lógica modular, drag & drop con `GridStack.js`.
  - Cada widget tiene tipo, filtros, configuración y datos gestionados por `DashboardWidgetService`.
- **Servicios:**
  - Todos los servicios CRUD siguen la convención de métodos (`filterAndPaginate`, `paginate`, `all`, `find`, `create`, `update`, `delete`).
  - Los servicios de sesión POS y facturación implementan validaciones de negocio y transacciones.
- **Controladores:**
  - Usan Inertia.js para renderizar páginas Vue o retornan JSON para APIs.
  - Reciben requests validados y delegan a servicios.
- **Keyboard Shortcuts:**
  - Atajos extensivos en la UI POS (`resources/js/pages/POS/Index.vue`), documentados en diálogos y comentarios.
- **Styling:**
  - Utility CSS tipo Tailwind, clases personalizadas en `resources/css/`.
  - Scrollbar y diálogos personalizados.
- **Internationalization:**
  - Soporte i18n implementado, seguir patrones existentes.
## 🔗 Integration & Dependencies
- **Frontend:** Vue 3, Inertia.js, Ziggy.js, Chart.js, GridStack.js
- **Backend:** Laravel, Eloquent ORM, PDF (dompdf), Carbon, Auth, DB
- **Docs:**
  - Widgets: `docs/WIDGETS_DOCUMENTATION.md`, `docs/WIDGETS_SUMMARY.md`
  - Implementación: `docs/IMPLEMENTATION_SUMMARY.md`
  - Ejemplo de integración PDF: `PdfController.php` usa `SystemSettingService` para plantilla dinámica
## 📝 Examples & References
- **Widget Example:** `resources/js/pages/Dashboard.vue`, componentes de widgets, y controladores `DashboardWidgetController.php`
- **Service Example:** `app/Services/ProductService.php`, `app/Services/InvoiceService.php`, `app/Services/DashboardWidgetService.php`
- **Controller-Service Pattern:** `ProductController.php` → `ProductService.php`, `CustomerController.php` → `CustomerService.php`
- **Keyboard Shortcuts:** `resources/js/pages/POS/Index.vue` (buscar `<kbd>`)

## 🗄️ Data Model & Migrations
- Las migraciones definen tablas para usuarios, productos, categorías, clientes, unidades de medida, facturas, items de factura, sesiones POS, widgets de dashboard y configuración del sistema.
- Convenciones:
  - Todas las tablas principales usan `id` autoincremental, timestamps y soft deletes (`softDeletes()`).
  - Relaciones: claves foráneas con `constrained()` y `cascadeOnDelete()` para integridad referencial.
  - Tablas de configuración (`system_settings`, `system_setting_options`, `user_system_settings`) permiten opciones y valores personalizados por usuario.
  - Widgets de dashboard (`dashboard_widgets`) soportan configuración JSON, filtros y filtros avanzados.
  - Facturas y sesiones POS incluyen campos de control de estado, totales, métodos de pago y notas.
- Para detalles de campos y relaciones, ver migraciones en `database/migrations/`.
## 🚩 Special Notes
- **Scroll/overflow issues:** Ver docs para fixes y scripts de testing (`docs/IMPLEMENTATION_SUMMARY.md`)
- **Performance:** Consultar docs para optimizaciones de queries, caché y lazy loading en widgets
- **Security:** Seguir buenas prácticas de Laravel para validación, acceso a datos y sanitización
- **Testing:** Verificación de scroll y UI en widgets, checklist en docs
- **Extensibilidad:** Para nuevos módulos, seguir patrón controlador-servicio-modelo y documentar en `docs/`

---
Para más detalles, consulta los archivos en `docs/` y los comentarios en el código. Si tienes dudas, sigue los patrones existentes y referencia la documentación de widgets y servicios.
# 🤖 Copilot Instructions for POS System

## 🏗️ Project Architecture
- **Backend:** Laravel (PHP) in `app/` (Controllers, Services, Models, Traits)
- **Frontend:** Vue 3 + TypeScript in `resources/js/` (pages, components, UI)
- **Dashboard Widgets:** Modular, documented in `docs/WIDGETS_DOCUMENTATION.md` and `docs/WIDGETS_SUMMARY.md`
- **Data Flow:** RESTful APIs and Inertia.js bridge backend and frontend. Services encapsulate business logic.
- **Key Patterns:**
  - Service classes in `app/Services/` for business logic
  - Models in `app/Models/` for Eloquent ORM
  - Vue components use Composition API and utility-first CSS
  - Widget config and docs: see `docs/`

## ⚡ Developer Workflows
- **Build:** `npm run build` (see VS Code task "Build POS System")
- **Dev:** `npm run dev` (for hot reload)
- **Test (PHP):** `php artisan test` or `vendor/bin/phpunit`
- **Test (JS):** No standard JS test suite; add as needed
- **Debug:** Use Laravel logs (`storage/logs/`), browser devtools, and Vue error overlays
- **Migrations/Seeders:** In `database/` (run with `php artisan migrate`, `db:seed`)

## 🧩 Project-Specific Conventions
- **Widget System:**
  - Widgets are defined, configured, and documented in `docs/WIDGETS_DOCUMENTATION.md`
  - Widget UI/logic is modular; follow existing patterns for new widgets
  - Use `GridStack.js` for drag/drop and resizing
- **Keyboard Shortcuts:**
  - Extensive shortcuts in POS UI (`resources/js/pages/POS/Index.vue`)
  - Documented in UI dialogs and code comments
- **Styling:**
  - Utility CSS (Tailwind-like), custom classes in `resources/css/`
  - Custom scrollbar and dialog styles
- **Internationalization:**
  - i18n support is implemented; follow existing patterns

## 🔗 Integration & Dependencies
- **Frontend:** Vue 3, Inertia.js, Ziggy.js, Chart.js, GridStack.js
- **Backend:** Laravel, Eloquent ORM
- **Docs:**
  - Widget docs: `docs/WIDGETS_DOCUMENTATION.md`, `docs/WIDGETS_SUMMARY.md`
  - Implementation notes: `docs/IMPLEMENTATION_SUMMARY.md`

## 📝 Examples & References
- **Widget Example:** See `resources/js/pages/Dashboard.vue` and widget components
- **Service Example:** See `app/Services/DashboardWidgetService.php`
- **Keyboard Shortcuts:** See `resources/js/pages/POS/Index.vue` (search for `<kbd>`)

## 🚩 Special Notes
- **Scroll/overflow issues:** See docs for fixes and testing scripts
- **Performance:** Use efficient queries, cache where possible, lazy load widget data
- **Security:** Follow Laravel best practices for data access and validation

---
For more, see `docs/` and code comments. When in doubt, follow existing patterns and reference the widget documentation.
