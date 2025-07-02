# Sistema POS - Point of Sale

Sistema completo de Punto de Venta desarrollado con Laravel 11, Inertia.js, Vue 3 y Pinia.

## Características

### Frontend
- **Vue 3** con Composition API
- **Pinia** para gestión de estado
- **Inertia.js** para SPA sin API
- **TailwindCSS** para diseño
- **TypeScript** para tipado fuerte
- **Componentes UI** modernos y responsivos

### Backend
- **Laravel 11** con PHP 8.2+
- **MySQL** como base de datos
- **Arquitectura de servicios** para lógica de negocio
- **Validación de formularios** robusta
- **Gestión de inventario** automática

### Funcionalidades

#### Sistema POS
- ✅ Interfaz de punto de venta intuitiva
- ✅ Búsqueda de productos en tiempo real
- ✅ Gestión de carrito de compra
- ✅ Selección de clientes
- ✅ Procesamiento de ventas
- ✅ Cálculo automático de cambio
- ✅ Gestión automática de inventario

#### Gestión de Facturas
- ✅ CRUD completo de facturas
- ✅ Estados: Pendiente, Pagada, Cancelada
- ✅ Filtros avanzados y búsqueda
- ✅ Paginación
- ✅ Vista detallada de facturas

#### Dashboard
- ✅ Estadísticas de ventas diarias y mensuales
- ✅ Gráfico de ventas de últimos 7 días
- ✅ Facturas recientes
- ✅ Alertas de stock bajo
- ✅ Productos sin stock
- ✅ Acciones rápidas

#### Gestión de Productos
- ✅ CRUD de productos con categorías
- ✅ Gestión de stock automática
- ✅ Precios y unidades de medida
- ✅ SKU único por producto

#### Gestión de Clientes
- ✅ Registro completo de clientes
- ✅ Búsqueda rápida en POS
- ✅ Historial de compras

## Instalación

### Requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd POS
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
Editar `.env` con tus credenciales de base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_system
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Instalar dependencias Node.js**
```bash
npm install
```

7. **Construir assets**
```bash
npm run build
```

## Desarrollo

### Servidor de desarrollo
```bash
# Terminal 1 - Servidor Laravel
php artisan serve

# Terminal 2 - Servidor Vite (desarrollo)
npm run dev
```

### Comandos útiles
```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Construir para producción
npm run build

# Linter
npm run lint
```

## Estructura del Proyecto

### Backend
```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── InvoiceController.php
│   ├── PosController.php
│   └── ...
├── Models/
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   ├── Product.php
│   └── ...
└── Services/
    ├── InvoiceService.php
    └── ...
```

### Frontend
```
resources/js/
├── pages/
│   ├── Dashboard.vue
│   ├── POS/Index.vue
│   └── Invoices/
├── stores/
│   ├── posStore.ts
│   └── invoiceStore.ts
├── components/ui/
└── composables/
```

## Modelos de Datos

### Products
- ID, Name, SKU, Price, Stock
- Category, Unit Measure
- Timestamps

### Customers
- ID, First Name, Last Name
- Email, Phone, Address
- Timestamps

### Invoices
- ID, Invoice Number
- Customer, User, Date
- Status, Total Amount
- Timestamps

### Invoice Items
- ID, Invoice ID, Product ID
- Quantity, Unit Price, Line Total
- Timestamps

## Estados de Factura
- **pending**: Pendiente de pago
- **paid**: Pagada
- **cancelled**: Cancelada

## API Endpoints

### POS
- `GET /pos` - Interfaz POS
- `POST /pos/sale` - Procesar venta
- `GET /pos/products/search` - Buscar productos
- `GET /pos/customers/search` - Buscar clientes

### Facturas
- `GET /invoices` - Listar facturas
- `GET /invoices/create` - Formulario crear
- `POST /invoices` - Crear factura
- `GET /invoices/{id}` - Ver factura
- `GET /invoices/{id}/edit` - Formulario editar
- `PUT /invoices/{id}` - Actualizar factura
- `DELETE /invoices/{id}` - Eliminar factura

### Dashboard
- `GET /dashboard` - Dashboard con estadísticas

## Tecnologías Utilizadas

### Frontend
- Vue 3
- Pinia
- Inertia.js
- TypeScript
- TailwindCSS
- Lucide Icons
- Heroicons

### Backend
- Laravel 11
- PHP 8.2+
- MySQL
- Eloquent ORM

### Herramientas de Desarrollo
- Vite
- ESLint
- Prettier
- PHP-CS-Fixer

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

## Soporte

Para soporte y preguntas, abre un issue en el repositorio.
