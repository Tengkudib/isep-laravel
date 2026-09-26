@echo off
REM Jalankan iSEP (Laravel 13) dengan PHP 8.4. Pastikan MySQL XAMPP sudah dihidupkan.
cd /d "%~dp0"
echo iSEP sedang berjalan di http://127.0.0.1:8000  (tekan Ctrl+C untuk berhenti)
"C:\php84\php.exe" artisan serve --host=127.0.0.1 --port=8000
