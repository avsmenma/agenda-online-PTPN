@echo off
REM ===================================================================
REM Script Deploy Otomatis ke Server Production
REM ===================================================================
REM Server: 163.61.58.92
REM Path: /var/www/agenda-online-PTPN
REM Branch: codinggemini
REM ===================================================================

echo.
echo ========================================
echo   AGENDA ONLINE - DEPLOYMENT SCRIPT
echo ========================================
echo.
echo Server: 163.61.58.92
echo Path: /var/www/agenda-online-PTPN
echo.
echo ========================================
echo   Step 1: Connecting to server...
echo ========================================
echo.

ssh root@163.61.58.92 "cd /var/www/agenda-online-PTPN && echo '=== Current Branch ===' && git branch && echo '' && echo '=== Git Status ===' && git status && echo '' && echo '=== Pulling Latest Changes ===' && git pull && echo '' && echo '=== Clearing All Caches ===' && php artisan cache:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear && echo '' && echo '=== DEPLOYMENT COMPLETED SUCCESSFULLY! ==="

echo.
echo ========================================
echo   Deployment completed!
echo ========================================
echo.
echo Next steps:
echo 1. Open browser: http://163.61.58.92/owner/cashbank
echo 2. Press Ctrl+Shift+R to hard refresh
echo 3. Check the new interactive donut chart
echo.
pause
