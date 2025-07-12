# Sistema de Filtros Avanzados - Ejemplos de Uso

## Descripción
El sistema de filtros avanzados permite crear consultas complejas con múltiples condiciones y operadores lógicos AND/OR.

## Estructura de Filtros Avanzados

### Tipos de Operadores por Campo

#### String (Texto)
- `is` / `equals`: Igual a
- `is_not` / `not_equals`: No igual a  
- `contains`: Contiene
- `not_contains`: No contiene
- `starts_with`: Empieza con
- `ends_with`: Termina con
- `is_empty`: Está vacío
- `is_not_empty`: No está vacío

#### Number (Números)
- `equals`: Igual a
- `not_equals`: No igual a
- `greater_than`: Mayor que
- `less_than`: Menor que
- `greater_equal`: Mayor o igual que
- `less_equal`: Menor o igual que
- `between`: Entre dos valores
- `is_null`: Es nulo
- `is_not_null`: No es nulo

#### Date (Fechas)
- `equals`: Igual a
- `not_equals`: No igual a
- `after`: Después de
- `before`: Antes de
- `between`: Entre dos fechas

#### Boolean
- `is_true`: Es verdadero
- `is_false`: Es falso

## Ejemplos de Filtros

### Ejemplo 1: Productos caros en categoría específica
```json
[
  {
    "operator": "AND",
    "filters": [
      {
        "field": "category_id",
        "operator": "equals",
        "value": "1",
        "type": "number"
      },
      {
        "field": "price",
        "operator": "greater_than",
        "value": "100",
        "type": "number"
      }
    ]
  }
]
```

### Ejemplo 2: Productos con stock bajo O descontinuados
```json
[
  {
    "operator": "OR",
    "filters": [
      {
        "field": "stock",
        "operator": "less_than",
        "value": "10",
        "type": "number"
      },
      {
        "field": "is_active",
        "operator": "is_false",
        "value": null,
        "type": "boolean"
      }
    ]
  }
]
```

### Ejemplo 3: Ventas del último mes con método específico
```json
[
  {
    "operator": "AND",
    "filters": [
      {
        "field": "date",
        "operator": "after",
        "value": "2024-12-01",
        "type": "date"
      },
      {
        "field": "payment_method",
        "operator": "equals",
        "value": "card",
        "type": "string"
      }
    ]
  }
]
```

### Ejemplo 4: Múltiples grupos con lógica compleja
```json
[
  {
    "operator": "AND",
    "filters": [
      {
        "field": "category_id",
        "operator": "equals",
        "value": "1",
        "type": "number"
      },
      {
        "field": "price",
        "operator": "greater_than",
        "value": "50",
        "type": "number"
      }
    ]
  },
  {
    "operator": "OR",
    "filters": [
      {
        "field": "stock",
        "operator": "less_than",
        "value": "5",
        "type": "number"
      },
      {
        "field": "name",
        "operator": "contains",
        "value": "urgente",
        "type": "string"
      }
    ]
  }
]
```

## Campos Disponibles por Fuente de Datos

### Productos
- `id` (number)
- `name` (string)
- `sku` (string)
- `price` (number)
- `stock` (number)
- `category_id` (number)
- `is_active` (boolean)
- `created_at` (date)

### Categorías  
- `id` (number)
- `name` (string)
- `description` (string)
- `is_active` (boolean)

### Clientes
- `id` (number)
- `name` (string)
- `email` (string)
- `phone` (string)
- `address` (string)
- `created_at` (date)

### Facturas
- `id` (number)
- `total_amount` (number)
- `status` (string)
- `payment_method` (string)
- `date` (date)
- `customer_id` (number)
- `user_id` (number)

### Items de Factura
- `id` (number)
- `quantity` (number)
- `unit_price` (number)
- `line_total` (number)
- `product_id` (number)
- `invoice_id` (number)

## Uso en la Interfaz

1. **Crear Widget**: Selecciona el tipo de widget y ve a la sección "Filtros Avanzados"
2. **Agregar Grupo**: Click en "Agregar Grupo de Filtros"
3. **Configurar Filtros**: Para cada grupo:
   - Selecciona el operador del grupo (AND/OR)
   - Agrega filtros individuales
   - Selecciona campo, operador y valor
4. **Previsualizar**: Los filtros se aplican automáticamente al widget

## Implementación Backend

El sistema procesa los filtros en `DashboardWidgetService::applyAdvancedFilters()`:
- Cada grupo se procesa como una subconsulta
- Los filtros dentro del grupo se combinan con el operador del grupo
- Los grupos se combinan con AND entre ellos
- Se mapean automáticamente los operadores a sintaxis SQL

## Consideraciones de Rendimiento

- Los filtros se aplican a nivel de base de datos
- Se recomienda usar índices en campos filtrados frecuentemente
- Para consultas complejas, considera agregar índices compuestos
- Los filtros se validan en el frontend y backend
