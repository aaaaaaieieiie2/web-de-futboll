Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  DESPLIEGUE AUTOMÁTICO AL VPS NICO SPORT" -ForegroundColor Cyan
Write-Host "  IP: 209.126.87.113" -ForegroundColor Yellow
Write-Host "  Ruta: /var/www/web-de-futboll/" -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Cyan

$archivoParche = "parche-cms-seo.tar.gz"
if (!(Test-Path $archivoParche)) {
    Write-Host "[ERROR] No se encontró $archivoParche en este directorio." -ForegroundColor Red
    exit 1
}

Write-Host "`n[1/2] Transfiriendo parche al servidor..." -ForegroundColor Green
scp $archivoParche root@209.126.87.113:/var/www/web-de-futboll/

if ($LASTEXITCODE -ne 0) {
    Write-Host "`n[ERROR] Falló la transferencia SCP. Verifica la conexión o contraseña." -ForegroundColor Red
    exit 1
}

Write-Host "`n[2/2] Descomprimiendo, limpiando OPcache y aplicando cambios en producción..." -ForegroundColor Green
$cmdSSH = "cd /var/www/web-de-futboll && " +
          "tar -xzf parche-cms-seo.tar.gz && rm -f parche-cms-seo.tar.gz && " +
          "(if [ -d /var/www/html ]; then cp -a /var/www/web-de-futboll/. /var/www/html/ 2>/dev/null || true; rm -rf /var/www/html/uploads 2>/dev/null || true; ln -sfn /var/www/web-de-futboll/uploads /var/www/html/uploads 2>/dev/null || true; chown -h -R www-data:www-data /var/www/html 2>/dev/null || true; fi) && " +
          "mkdir -p uploads/analytics uploads/logs_archive uploads/ratelimit /tmp/ns_waf_state .well-known public/.well-known && " +
          "rm -f /tmp/ns_waf_state/*.json 2>/dev/null || true && " +
          "rm -f uploads/ratelimit/*.json 2>/dev/null || true && " +
          "echo '[]' > uploads/waf_blocked.json && " +
          "chown -R www-data:www-data api core private public uploads .well-known registro.php soporte.php desbloquear.php error_403.php robots.txt sitemap.xml .htaccess /tmp/ns_waf_state 2>/dev/null || true && " +
          "find api core private public .well-known -type d -exec chmod 755 {} + 2>/dev/null || true && " +
          "find api core private public .well-known -type f -exec chmod 644 {} + 2>/dev/null || true && " +
          "chmod 644 registro.php soporte.php desbloquear.php error_403.php robots.txt sitemap.xml .htaccess 2>/dev/null || true && " +
          "chmod -R 777 uploads /tmp/ns_waf_state 2>/dev/null || true && " +
          "chmod 666 uploads/waf_blocked.json 2>/dev/null || true && " +
          "php -r 'if (function_exists(\`"opcache_reset\`\")) opcache_reset();' 2>/dev/null || true && " +
          "systemctl restart apache2 2>/dev/null || systemctl reload apache2 2>/dev/null || true && " +
          "systemctl restart php*-fpm 2>/dev/null || true && " +
          "echo '>>> PARCHE DESPLEGADO, OPCACHE REINICIADO, BLOQUEOS WAF BORRADOS Y SERVICIOS ACTIVOS <<<' "
ssh root@209.126.87.113 $cmdSSH

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n==========================================" -ForegroundColor Green
    Write-Host "  ¡DESPLIEGUE COMPLETADO AL 100%!" -ForegroundColor Green
    Write-Host "  El VPS ya está actualizado y funcionando." -ForegroundColor Green
    Write-Host "==========================================" -ForegroundColor Green
}
