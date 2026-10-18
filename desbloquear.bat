@echo off
echo ==========================================================
echo       DESBLOQUEO TOTAL DE IP EN EL SERVIDOR VPS
echo ==========================================================
echo Conectando a 209.126.87.113 para borrar todos los bloqueos...
echo.
powershell -NoProfile -ExecutionPolicy Bypass -Command "ssh root@209.126.87.113 'rm -rf /tmp/ns_waf_state/* /tmp/ns_waf_blocked.json /var/www/web-de-futboll/uploads/ratelimit/* && echo \"[]\" > /var/www/web-de-futboll/uploads/waf_blocked.json && (if [ -d /var/www/html/uploads ]; then echo \"[]\" > /var/www/html/uploads/waf_blocked.json; fi) && chmod 666 /var/www/web-de-futboll/uploads/waf_blocked.json 2>/dev/null || true && echo === TODAS LAS IPs HAN SIDO DESBLOQUEADAS CON EXITO ===' "
echo.
echo ==========================================================
echo Si viste 'TODAS LAS IPs HAN SIDO DESBLOQUEADAS', ya puedes
echo entrar a academianicosport.com en tu navegador.
echo ==========================================================
pause
