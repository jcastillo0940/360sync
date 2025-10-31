#!/bin/bash

EXECUTION_ID=${1:-83}

# Pedir contraseña una sola vez
echo "Ingresa la contraseña de MySQL root:"
read -s MYSQL_PWD
export MYSQL_PWD

# Verificar que la contraseña funciona
mysql -u root 360sync -e "SELECT 1" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "❌ Contraseña incorrecta"
    exit 1
fi

echo "✅ Conectado correctamente"
echo ""

while true; do
    clear
    echo "========================================="
    echo "Execution #$EXECUTION_ID - $(date '+%H:%M:%S')"
    echo "========================================="
    echo ""
    
    # Estado general
    echo "📊 Estado General:"
    mysql -u root 360sync -se "
    SELECT 
        CONCAT('Status: ', status) as info
    FROM executions WHERE id = $EXECUTION_ID
    UNION ALL
    SELECT CONCAT('Started: ', started_at) FROM executions WHERE id = $EXECUTION_ID
    UNION ALL
    SELECT CONCAT('Running: ', TIMESTAMPDIFF(MINUTE, started_at, NOW()), ' minutes') FROM executions WHERE id = $EXECUTION_ID
    UNION ALL
    SELECT CONCAT('Processed: ', processed_items, '/', IFNULL(total_items, 0)) FROM executions WHERE id = $EXECUTION_ID;
    " 2>/dev/null
    
    echo ""
    echo "📈 Resumen de Logs:"
    mysql -u root 360sync -se "
    SELECT 
        level,
        COUNT(*) as count
    FROM execution_logs 
    WHERE execution_id = $EXECUTION_ID 
    GROUP BY level;
    " 2>/dev/null
    
    echo ""
    echo "📝 Últimos 10 logs:"
    mysql -u root 360sync -se "
    SELECT 
        DATE_FORMAT(logged_at, '%H:%i:%s') as time,
        RPAD(level, 8, ' ') as level,
        LEFT(message, 70) as message
    FROM execution_logs 
    WHERE execution_id = $EXECUTION_ID 
    ORDER BY logged_at DESC 
    LIMIT 10;
    " 2>/dev/null | column -t
    
    echo ""
    echo "Presiona Ctrl+C para salir"
    sleep 3
done
