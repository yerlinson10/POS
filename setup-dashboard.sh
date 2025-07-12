#!/bin/bash

# Script de Configuración del Dashboard Dinámico
# Ejecutar después de la instalación inicial

echo "🚀 Configurando Dashboard Dinámico..."

# 1. Ejecutar migraciones
echo "📊 Ejecutando migraciones..."
php artisan migrate

# 2. Limpiar caché de configuración
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Crear enlaces simbólicos si es necesario
echo "🔗 Verificando enlaces simbólicos..."
php artisan storage:link

# 4. Compilar assets para producción
echo "⚡ Compilando assets..."
npm run build

echo "✅ ¡Dashboard Dinámico configurado correctamente!"
echo ""
echo "📝 Accesos disponibles:"
echo "   - Dashboard Normal: /dashboard"
echo "   - Dashboard Dinámico: /dashboard/dynamic"
echo ""
echo "🎯 Próximos pasos:"
echo "   1. Accede a /dashboard/dynamic"
echo "   2. Haz clic en 'Agregar Widget' para crear tu primer widget"
echo "   3. Personaliza los filtros y la apariencia según tus necesidades"
echo ""
echo "📚 Consulta DASHBOARD_DINAMICO_README.md para más información"
