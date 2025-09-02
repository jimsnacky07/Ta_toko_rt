# 📋 Panduan Penggunaan Sistem Toko RT

## 🚀 Cara Menjalankan Aplikasi

### 1. Start Server
```bash
php artisan serve
```
Aplikasi akan berjalan di: `http://127.0.0.1:8000`

### 2. Akses Browser Preview
Klik tombol browser preview yang muncul untuk mengakses aplikasi.

## 👥 Data Login

### 🔑 Kredensial Login

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| **Admin** | admin@tokort.com | zxcvbnm123 | Dashboard Admin, Kelola Pesanan |
| **Tailor** | tailor@tokort.com | zxcvbnm123 | Dashboard Tailor, Update Status |
| **User 1** | siti@user.com | zxcvbnm123 | Dashboard User, Buat Pesanan |
| **User 2** | budi@user.com | zxcvbnm123 | Dashboard User, Buat Pesanan |
| **User 3** | rina@user.com | zxcvbnm123 | Dashboard User, Buat Pesanan |

*Dan 6 user lainnya dengan email: ahmad@user.com, dewi@user.com, rudi@user.com, maya@user.com, indra@user.com, lestari@user.com*

## 🎯 Alur Penggunaan Sistem

### **Untuk Admin:**
1. Login dengan `admin@tokort.com`
2. Akses Dashboard Admin
3. Lihat daftar pesanan di menu "Daftar Pesanan"
4. Kelola pesanan dan assign ke tailor

### **Untuk Customer/User:**
1. Login dengan salah satu akun user
2. Browse produk di dashboard
3. Tambah ke keranjang
4. Checkout dan bayar via Midtrans
5. **Pesanan otomatis masuk ke database setelah pembayaran sukses**
6. Lihat status pesanan di "Pesanan Saya"

### **Untuk Tailor:**
1. Login dengan `tailor@tokort.com`
2. Lihat pesanan yang di-assign
3. Update status produksi

## 🛒 Alur Pembayaran yang Sudah Diperbaiki

### **Sebelumnya (Bermasalah):**
❌ Pembayaran sukses tapi pesanan tidak masuk database

### **Sekarang (Sudah Diperbaiki):**
✅ **Alur Pembayaran Baru:**
1. User pilih produk → `KeranjangController.pay()`
2. Data pesanan disimpan ke session sebagai `pending_order`
3. Redirect ke Midtrans untuk pembayaran
4. **Webhook Midtrans** → `MidtransController.notification()`
5. **Otomatis buat order + order_items** di database
6. Hapus item dari keranjang
7. User bisa lihat pesanan di "Pesanan Saya"

## 🔧 URL Testing & Debug

### **Test Login Cepat:**
- `/test-admin-login` - Auto login sebagai admin
- `/check-users` - Lihat semua user di database
- `/debug-orders` - Debug data pesanan

### **Setup & Maintenance:**
- `/setup-all` - Setup database dan sample data
- `/debug-products` - Cek data produk

## 📊 Struktur Database

### **Tabel Users:**
- `id`, `name`, `nama`, `email`, `password`
- `level` (admin/tailor/user), `no_telp`, `alamat`

### **Tabel Orders:**
- `id`, `user_id`, `order_code`, `status`, `total_amount`
- `paid_at`, `created_at`, `updated_at`

### **Tabel Order Items:**
- `id`, `order_id`, `product_id`, `garment_type`
- `fabric_type`, `size`, `price`, `quantity`, `total_price`

## 🎉 Fitur yang Sudah Berfungsi

✅ **Login System** - Multi-level (admin/tailor/user)
✅ **Admin Dashboard** - Kelola pesanan dan user
✅ **Pembayaran Midtrans** - Terintegrasi dengan webhook
✅ **Database Orders** - Pesanan otomatis masuk setelah bayar
✅ **User Management** - 11 user sample (1 admin, 1 tailor, 9 user)
✅ **Product Catalog** - Browse dan detail produk
✅ **Shopping Cart** - Tambah/hapus item keranjang

## 🚨 Troubleshooting

### **Jika Login Tidak Bisa:**
1. Pastikan database users terisi (jalankan `/check-users`)
2. Cek password: semua user menggunakan `zxcvbnm123`

### **Jika Pesanan Tidak Masuk:**
1. Cek webhook Midtrans sudah dikonfigurasi
2. Lihat log di `/debug-orders`
3. Pastikan session `pending_order` ada

### **Jika Error Database:**
1. Jalankan `/setup-all` untuk reset database
2. Cek koneksi database di `.env`

## 📞 Support

Jika ada masalah, cek:
1. Log Laravel di `storage/logs/`
2. Debug routes yang tersedia
3. Browser console untuk error JavaScript

---
**Sistem sudah siap digunakan! 🎊**
