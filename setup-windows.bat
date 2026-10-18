@echo off
rem Bam dup file nay de cai dat QLNS tren Windows 10/11 (chi can chay 1 lan).
chcp 65001 >nul
title QLNS - Cai dat cho Windows
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\windows\setup.ps1"
echo.
pause
