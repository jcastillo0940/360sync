#!/bin/bash

echo "==========================================="
echo "360Sync - VERIFICACIÓN FINAL"
echo "==========================================="
echo ""

cd /var/www/360sync

echo "✅ 1. SISTEMA OPERATIVO Y VERSIONES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  OS: $(lsb_release -d | cut -f2)"
php -v | head -n 1
php artisan --version
echo ""

echo "✅ 2. EXTENSIONES PHP CRÍTICAS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
for ext in intl pdo_mysql mbstring curl xml; do
    printf "  %-12s: " "$ext"
    php -m | grep -q "^$ext$" && echo "✅" || echo "❌"
done
echo ""

echo "✅ 3. BASE DE DATOS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan db:show 2>&1 | grep -E "MySQL|Database|Tables" | head -n 3
echo ""

echo "✅ 4. QUEUE WORKERS (Supervisor)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
sudo supervisorctl status 360sync-worker:*
echo ""

echo "✅ 5. CRONTAB CONFIGURADO"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
crontab -l | grep -v "^#" | grep -v "^$" | head -n 5
echo ""

echo "✅ 6. PRÓXIMAS TAREAS PROGRAMADAS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan schedule:list 2>&1 | head -n 8
echo ""

echo "✅ 7. MODELOS EXISTENTES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
ls -1 /var/www/360sync/app/Models/ | sed 's/^/  /'
echo ""

echo "✅ 8. COMANDOS ARTISAN PERSONALIZADOS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan list 2>&1 | grep -E "sync:|magento:|logs:" | sed 's/^/  /'
echo ""

echo "✅ 9. PERMISOS Y ESPACIO EN DISCO"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
ls -ld storage bootstrap/cache | awk '{print "  "$1" "$3":"$4" "$9}'
df -h /var/www/360sync | tail -n 1 | awk '{print "  Disco: "$3"/"$2" ("$5" usado)"}'
echo ""

echo "✅ 10. ESTADO DE JOBS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan queue:monitor database 2>&1 | head -n 6 | sed 's/^/  /'
echo ""

echo "==========================================="
echo "🎉 MIGRACIÓN DE WINDOWS A UBUNTU COMPLETADA"
echo "==========================================="
echo ""
echo "📋 COMANDOS ÚTILES PARA UBUNTU:"
echo ""
echo "  Supervisor (Workers):"
echo "    sudo supervisorctl status             # Ver estado"
echo "    sudo supervisorctl restart 360sync-worker:*  # Reiniciar"
echo "    sudo supervisorctl stop 360sync-worker:*     # Detener"
echo ""
echo "  Logs en tiempo real:"
echo "    tail -f storage/logs/worker.log       # Workers"
echo "    tail -f storage/logs/laravel.log      # Laravel"
echo ""
echo "  Tareas programadas:"
echo "    php artisan schedule:list             # Ver próximas"
echo "    php artisan schedule:run              # Ejecutar ahora"
echo ""
echo "  Mantenimiento:"
echo "    php artisan logs:clean --days=30      # Limpiar logs"
echo "    php artisan queue:failed              # Ver jobs fallidos"
echo "    php artisan queue:retry all           # Reintentar fallidos"
echo ""
echo "  Comandos de sincronización:"
echo "    php artisan sync:products             # Sync productos"
echo "    php artisan sync:categories           # Sync categorías"
echo "    php artisan magento:sync-skus         # Sync SKUs"
echo ""
