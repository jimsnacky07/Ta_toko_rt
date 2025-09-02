@echo off
echo Membersihkan migrasi yang tidak diperlukan...

echo.
echo 1. Menghapus file migrasi yang tidak diperlukan...
del "database\migrations\2025_08_21_215106_create_tailors_table.php" 2>nul
del "database\migrations\2025_08_21_215501_add_user_to_tailors.php" 2>nul
del "database\migrations\2025_08_11_120122_create_pesanan_table.php" 2>nul
del "database\migrations\2025_08_12_114339_create_pembayarans_table.php" 2>nul
del "database\migrations\2025_08_13_145458_create_customer_request_table.php" 2>nul
del "database\migrations\2025_08_13_145516_create_detail_pesanan_table.php" 2>nul

echo.
echo 2. Membersihkan cache migrasi...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo.
echo 3. Menjalankan migrasi yang tersisa...
php artisan migrate

echo.
echo 4. Menjalankan seeder...
php artisan db:seed

echo.
echo Proses selesai! Database sudah bersih dan siap digunakan.
echo.
pause
