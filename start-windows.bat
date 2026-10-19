@echo off
rem Bam dup file nay de chay QLNS (MySQL + web server + trinh duyet + Kiosk app).
chcp 65001 >nul
title QLNS - Khoi dong
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\windows\start.ps1"
echo.
pause
