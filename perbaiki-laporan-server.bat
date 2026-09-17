@echo off
REM ============================================================
REM  PERBAIKAN LAPORAN HHK/HHBK - dijalankan DI SERVER (klik 2x)
REM  Tidak perlu SSH. Jalankan file ini langsung di komputer
REM  server Windows (C:\laragon\www\sibadak).
REM ============================================================

set "APP_DIR=C:\laragon\www\sibadak"
set "GIT_EXE=C:\laragon\bin\git\cmd\git.exe"
set "PHP_EXE=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"
set "COMPOSER_PHAR=C:\ProgramData\ComposerSetup\bin\composer.phar"
set BRANCH=main

if not exist "%GIT_EXE%" set "GIT_EXE=git"
if not exist "%PHP_EXE%" set "PHP_EXE=php"

echo ======================================================
echo  PERBAIKAN LAPORAN HHK/HHBK
echo ======================================================
echo.

cd /d "%APP_DIR%" || (echo Gagal masuk ke %APP_DIR% & pause & exit /b 1)

echo [1/3] Git pull...
"%GIT_EXE%" pull origin %BRANCH%
if errorlevel 1 (echo. & echo Git pull GAGAL. Cek internet/koneksi ke GitHub. & pause & exit /b 1)

echo.
echo [2/3] Regenerasi autoload Composer...
if exist "%COMPOSER_PHAR%" (
    "%PHP_EXE%" "%COMPOSER_PHAR%" dump-autoload -o
) else (
    where composer >nul 2>nul && (call composer dump-autoload -o) || (echo Composer tidak ditemukan, lewati - fallback autoload tetap menutupi.)
)

echo.
echo [3/3] Migrasi database...
"%PHP_EXE%" migrate.php

echo.
echo ======================================================
echo  SELESAI. Buka halaman /laporan di browser untuk tes.
echo ======================================================
pause
