@echo off
setlocal enabledelayedexpansion

:: =======================================================
:: KONFIGURASI SERVER (CLOUDFLARE SSH TUNNEL & WINDOWS)
:: =======================================================
set SERVER_USER=adit
set SERVER_IP=127.0.0.1
set SERVER_PORT=2222
set "REMOTE_DIR=C:\laragon\www\sibadak"
set BRANCH=main

:: Path penuh di server (SSH Windows PATH sering kosong/minimal)
set "REMOTE_GIT=C:\laragon\bin\git\cmd\git.exe"
set "REMOTE_PHP=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"
set "REMOTE_COMPOSER=C:\ProgramData\ComposerSetup\bin\composer.phar"

echo ======================================================
echo SIBADAK — DEPLOY GIT PULL + MIGRASI DB
echo ======================================================

:: 1. Commit (jika ada perubahan) lalu Push ke remote
echo [1/4] Mendorong perubahan lokal ke Repository...

git add .

set "NEED_COMMIT=0"
for /f %%i in ('git diff --cached --name-only') do set "NEED_COMMIT=1"

if "!NEED_COMMIT!"=="0" (
    echo Tidak ada perubahan lokal. Skip commit, lanjut push/pull...
) else (
    set /p msg="Masukkan pesan commit (tekan Enter untuk default 'update aplikasi'): "
    if "!msg!"=="" set "msg=update aplikasi"

    git commit -m "!msg!"
    if errorlevel 1 (
        echo Gagal melakukan git commit!
        pause
        exit /b 1
    )
)

git push origin %BRANCH%
if errorlevel 1 (
    echo Gagal melakukan git push dari laptop!
    pause
    exit /b 1
)

:: 2. Git Pull di Server via SSH
:: Shell remote sudah cmd — jangan bungkus cmd /c lagi (PATH SSH tanpa System32)
echo.
echo [2/4] Menjalankan 'git pull' di server...
echo *(Jika diminta password SSH, masukkan password akun server)*
echo.

ssh -p %SERVER_PORT% %SERVER_USER%@%SERVER_IP% "cd /d %REMOTE_DIR% && %REMOTE_GIT% pull origin %BRANCH%"

if errorlevel 1 (
    echo.
    echo Git Pull di server gagal.
    pause
    exit /b 1
)

:: 3. Regenerasi Composer autoload di Server (vendor/ di-gitignore,
:: classmap bisa kedaluwarsa setelah controller/model baru di-push)
echo.
echo [3/4] Meregenerasi Composer autoload di server...

ssh -p %SERVER_PORT% %SERVER_USER%@%SERVER_IP% "cd /d %REMOTE_DIR% && %REMOTE_PHP% %REMOTE_COMPOSER% dump-autoload -o"

if errorlevel 1 (
    echo.
    echo Composer dump-autoload di server gagal. Fallback autoload di config/config.php
    echo akan menutupi sementara, tapi segera perbaiki manual via SSH.
)

:: 4. Jalankan PHP Migration di Server via SSH
echo.
echo [4/4] Menjalankan migrasi database di server...

ssh -p %SERVER_PORT% %SERVER_USER%@%SERVER_IP% "cd /d %REMOTE_DIR% && %REMOTE_PHP% migrate.php"

if errorlevel 1 (
    echo.
    echo Migrasi CLI server gagal. Anda juga bisa membuka migrate.php di browser di server.
)

echo.
echo ======================================================
echo DEPLOYMENT DAN MIGRASI SELESAI!
echo ======================================================
pause
