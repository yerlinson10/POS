-- Insertar categorías de prueba
INSERT OR IGNORE INTO categories (id, name, description, created_at, updated_at) VALUES
(1, 'Bebidas', 'Refrescos y bebidas', datetime('now'), datetime('now')),
(2, 'Snacks', 'Comidas rápidas y aperitivos', datetime('now'), datetime('now')),
(3, 'Limpieza', 'Productos de limpieza', datetime('now'), datetime('now'));

-- Insertar unidades de medida
INSERT OR IGNORE INTO unit_measures (id, code, name, created_at, updated_at) VALUES
(1, 'UND', 'Unidad', datetime('now'), datetime('now')),
(2, 'KG', 'Kilogramo', datetime('now'), datetime('now')),
(3, 'LT', 'Litro', datetime('now'), datetime('now'));

-- Insertar productos de prueba
INSERT OR IGNORE INTO products (id, sku, name, description, category_id, unit_measure_id, price, stock, created_at, updated_at) VALUES
(1, 'COCA001', 'Coca Cola 350ml', 'Bebida gaseosa', 1, 1, 2500.00, 5, datetime('now'), datetime('now')),
(2, 'PEPS001', 'Pepsi 350ml', 'Bebida gaseosa', 1, 1, 2400.00, 8, datetime('now'), datetime('now')),
(3, 'CHIP001', 'Papas Fritas', 'Snack salado', 2, 1, 3500.00, 15, datetime('now'), datetime('now')),
(4, 'AGUA001', 'Agua Botella 500ml', 'Agua purificada', 1, 1, 1500.00, 20, datetime('now'), datetime('now')),
(5, 'DETG001', 'Detergente', 'Detergente en polvo', 3, 1, 8500.00, 3, datetime('now'), datetime('now'));

-- Insertar clientes de prueba
INSERT OR IGNORE INTO customers (id, first_name, last_name, email, phone, address, created_at, updated_at) VALUES
(1, 'Juan', 'Pérez', 'juan.perez@email.com', '555-1234', 'Calle 123 #45-67', datetime('now'), datetime('now')),
(2, 'María', 'González', 'maria.gonzalez@email.com', '555-5678', 'Carrera 89 #12-34', datetime('now'), datetime('now')),
(3, 'Carlos', 'López', 'carlos.lopez@email.com', '555-9012', 'Avenida 45 #78-90', datetime('now'), datetime('now')),
(4, 'Ana', 'Martínez', 'ana.martinez@email.com', '555-3456', 'Calle 67 #23-45', datetime('now'), datetime('now'));

-- Insertar usuarios de prueba (si no existe uno)
INSERT OR IGNORE INTO users (id, name, email, password, created_at, updated_at) VALUES
(1, 'Administrador', 'admin@pos.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxkUJqfuVJ5TRnNUSOVJSXoTgF6', datetime('now'), datetime('now'));

-- Insertar facturas de prueba (últimos 7 días)
INSERT OR IGNORE INTO invoices (id, customer_id, user_id, date, subtotal, total_amount, payment_method, status, created_at, updated_at) VALUES
-- Hoy
(1, 1, 1, datetime('now'), 6000.00, 6000.00, 'cash', 'paid', datetime('now'), datetime('now')),
(2, 2, 1, datetime('now'), 4000.00, 4000.00, 'card', 'paid', datetime('now'), datetime('now')),

-- Ayer
(3, 3, 1, datetime('now', '-1 day'), 8500.00, 8500.00, 'cash', 'paid', datetime('now', '-1 day'), datetime('now', '-1 day')),
(4, 4, 1, datetime('now', '-1 day'), 5500.00, 5500.00, 'card', 'paid', datetime('now', '-1 day'), datetime('now', '-1 day')),

-- Hace 2 días
(5, 1, 1, datetime('now', '-2 days'), 12000.00, 12000.00, 'cash', 'paid', datetime('now', '-2 days'), datetime('now', '-2 days')),

-- Hace 3 días
(6, 2, 1, datetime('now', '-3 days'), 7500.00, 7500.00, 'card', 'paid', datetime('now', '-3 days'), datetime('now', '-3 days')),

-- Hace 4 días
(7, 3, 1, datetime('now', '-4 days'), 3000.00, 3000.00, 'cash', 'paid', datetime('now', '-4 days'), datetime('now', '-4 days')),

-- Hace 5 días
(8, 4, 1, datetime('now', '-5 days'), 9500.00, 9500.00, 'card', 'paid', datetime('now', '-5 days'), datetime('now', '-5 days')),

-- Hace 6 días
(9, 1, 1, datetime('now', '-6 days'), 6500.00, 6500.00, 'cash', 'paid', datetime('now', '-6 days'), datetime('now', '-6 days')));

-- Insertar items de factura
INSERT OR IGNORE INTO invoice_items (id, invoice_id, product_id, quantity, unit_price, line_total, created_at, updated_at) VALUES
-- Factura 1
(1, 1, 1, 2, 2500.00, 5000.00, datetime('now'), datetime('now')),
(2, 1, 4, 1, 1500.00, 1500.00, datetime('now'), datetime('now')),

-- Factura 2
(3, 2, 2, 1, 2400.00, 2400.00, datetime('now'), datetime('now')),
(4, 2, 3, 1, 3500.00, 3500.00, datetime('now'), datetime('now')),

-- Factura 3
(5, 3, 5, 1, 8500.00, 8500.00, datetime('now', '-1 day'), datetime('now', '-1 day')),

-- Factura 4
(6, 4, 1, 2, 2500.00, 5000.00, datetime('now', '-1 day'), datetime('now', '-1 day')),

-- Factura 5
(7, 5, 3, 3, 3500.00, 10500.00, datetime('now', '-2 days'), datetime('now', '-2 days')),
(8, 5, 4, 1, 1500.00, 1500.00, datetime('now', '-2 days'), datetime('now', '-2 days')),

-- Factura 6
(9, 6, 2, 3, 2400.00, 7200.00, datetime('now', '-3 days'), datetime('now', '-3 days')),

-- Factura 7
(10, 7, 4, 2, 1500.00, 3000.00, datetime('now', '-4 days'), datetime('now', '-4 days')),

-- Factura 8
(11, 8, 1, 3, 2500.00, 7500.00, datetime('now', '-5 days'), datetime('now', '-5 days')),
(12, 8, 3, 1, 3500.00, 3500.00, datetime('now', '-5 days'), datetime('now', '-5 days')),

-- Factura 9
(13, 9, 2, 2, 2400.00, 4800.00, datetime('now', '-6 days'), datetime('now', '-6 days')),
(14, 9, 4, 1, 1500.00, 1500.00, datetime('now', '-6 days'), datetime('now', '-6 days'));
