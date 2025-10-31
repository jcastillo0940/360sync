#!/bin/bash

echo "==================================="
echo "360Sync - Verificación del Sistema"
echo "==================================="
echo ""

cd /var/www/360sync

echo "📌 Versiones:"
php -v | head -n 1
php artisan --version
echo ""

echo "🗄️ Base de datos:"
php artisan db:show 2>&1 | grep -E "MySQL|Connection|Database|Tables" | head -n 5
echo ""

echo "👥 Workers activos:"
sudo supervisorctl status 360sync-worker:* 2>&1
echo ""

echo "📋 Próximas tareas programadas:"
php artisan schedule:list 2>&1 | head -n 10
echo ""

echo "🔍 Jobs en cola:"
php artisan queue:monitor database 2>&1 | grep -E "Queue|Pending|jobs"
echo ""

echo "📝 Últimas 5 líneas del log de workers:"
tail -n 5 storage/logs/worker.log 2>/dev/null || echo "Sin logs de workers"
echo ""

echo "📝 Últimos errores en Laravel:"
grep -i "error" storage/logs/laravel.log 2>/dev/null | tail -n 3 || echo "Sin errores recientes"
echo ""

echo "💾 Espacio en disco:"
df -h /var/www/360sync | tail -n 1 | awk '{print "Usado: "$3" / "$2" ("$5")"}'
echo ""

echo "🔐 Permisos críticos:"
ls -ld storage bootstrap/cache
echo ""

echo "==================================="
echo "✅ Verificación completada"
echo "==================================="
