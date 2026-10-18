@echo off
echo ==========================================
echo   DESPLIEGUE AUTOMATICO AL VPS NICO SPORT
echo ==========================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0subir_a_vps.ps1"
pause
