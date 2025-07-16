# Sistema POS

Este proyecto es un sistema de Punto de Venta (POS) que permite la gestión de productos, ventas, clientes, inventario, sesiones de caja y reportes, todo desde una interfaz web moderna y segura.

## Características principales

- Gestión de productos, categorías y unidades de medida
- Registro y administración de clientes
- Facturación y control de ventas
- Control de stock y alertas de bajo inventario
- Gestión de sesiones de caja (apertura/cierre)
- Panel de control con estadísticas y widgets personalizables
- Configuración general y por usuario

---

## Requisitos del sistema

Para instalar y ejecutar el sistema necesitas:

- **PHP >= 8.1**
- **Composer** (gestor de dependencias PHP)
- **Node.js >= 18.x** y **npm**
- **MySQL >= 8.x**
- **Servidor web** (recomendado: Apache, Nginx o Laragon para desarrollo en Windows)
- **Extensiones PHP**: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo

---

## Instalación

1. **Clona el repositorio:**

   ```bash
   git clone https://github.com/yerlinson10/POS.git
   cd POS
   ```

2. **Copia el archivo de entorno:**

   ```bash
   cp .env.example .env
   # O en Windows:
   copy .env.example .env
   ```

3. **Configura las variables de entorno**
   - Edita el archivo `.env` y coloca los datos de tu base de datos y configuración local.

4. **Instala las dependencias de PHP:**

   ```bash
   composer install
   ```

5. **Instala las dependencias de Node.js:**

   ```bash
   npm install
   ```

6. **Genera la clave de la aplicación:**

   ```bash
   php artisan key:generate
   ```

7. **Ejecuta las migraciones y seeders:**

   ```bash
   php artisan migrate --seed
   ```

8. **Compila los assets del frontend:**

   ```bash
   npm run build
   # O para desarrollo:
   npm run dev
   ```

9. **Inicia el servidor de desarrollo:**

   ```bash
   php artisan serve
   ```
   Accede a la aplicación en [http://localhost:8000](http://localhost:8000)

---


## Primer acceso

- Al ejecutar las migraciones y seeders, el sistema crea un usuario de ejemplo para acceder por primera vez:
  - **Usuario:** admin@admin.com
  - **Contraseña:** admin1234
- Puedes modificar este usuario en la base de datos o crear uno nuevo desde la interfaz del sistema.

---

## Notas adicionales

- Para desarrollo en Windows se recomienda usar **Laragon** por su facilidad de configuración.
- Si usas otro entorno, asegúrate de que los puertos y servicios estén correctamente configurados.
- El sistema utiliza Inertia.js y Vue.js para el frontend, por lo que es necesario tener Node.js y npm instalados.
- Para enviar correos, configura los parámetros SMTP en el archivo `.env`.

---

## Licencia

Este proyecto es propiedad de Yerlinson Lora. Uso exclusivo para fines académicos y empresariales autorizados.

---

¿Dudas o problemas? Contacta al desarrollador principal o revisa la documentación técnica incluida en la carpeta `docs/`.
