<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\MidtransController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\GaleriJahitController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\User\ProductController as UserProductController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Tailor\DashboardController as TailorDashboardController;
use App\Http\Controllers\Tailor\RiwayatPesananController;
use App\Http\Controllers\Tailor\DataPesananController;
use App\Http\Controllers\User\KeranjangController;
use App\Http\Controllers\User\OrderCustomController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\AdminOrderController;

/*
|--------------------------------------------------------------------------
| PUBLIC / GUEST
|--------------------------------------------------------------------------
*/

// Landing (yang berlaku terakhir di kode aslinya)
Route::get('/', [LandingController::class, 'index'])->name('landinghome');

// Auth: Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Register
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Lupa Password
Route::get('/forgot-password', fn () => view('auth.forgot-password'))
    ->middleware('guest')
    ->name('password.request');

// Buat pesanan 
Route::get('/buat-pesanan', function () {
    return view('user.pesanan.order_custom'); 
})->name('buat.pesanan');

//keranjang
Route::middleware(['auth'])->group(function () {
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');  // Pastikan ini ada
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED (COMMON)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard generic (dipakai di kode)
    Route::get('/dashboard', fn () => view('user.dashboard'))->name('dashboard');

    // Profil
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');

    // Pusat Pesanan (alias)
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pusat.pesanan');
    Route::get('/user/orders', [PesananController::class, 'index'])->name('user.orders.index');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'level:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Daftar Pesanan
    Route::get('/daftar-pesanan', [OrderController::class, 'index'])->name('daftar.pesanan');
    Route::get('/daftar-pesanan/{pesanan}', [OrderController::class, 'show'])->name('daftar.pesanan.show');
    Route::patch('/daftar-pesanan/{pesanan}/status', [OrderController::class, 'updateStatus'])->name('daftar.pesanan.updateStatus');

    // Galeri Jahit (resource + custom names)
    Route::resource('galeri-jahit', GaleriJahitController::class)
        ->parameters(['galeri-jahit' => 'product'])
        ->names([
            'index'   => 'galeri.jahit.index',
            'create'  => 'galeri.jahit.create',
            'store'   => 'galeri.jahit.store',
            'show'    => 'galeri.jahit.show',
            'edit'    => 'galeri.jahit.edit',
            'update'  => 'galeri.jahit.update',
            'destroy' => 'galeri.jahit.destroy',
        ]);

    // Pelanggan (semua varian tetap ada endpoint-nya)
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan'); // admin.pelanggan
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan'); // duplicate as-is (nama sama)
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan'); // dijaga agar alur tidak berubah

    // Versi dengan nama berbeda yang juga ada di kode
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan');
    Route::get('/pelanggan/{id}', [PelangganController::class, 'show'])->name('pelanggan.show');
    Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');

    // Alias yang pernah dipakai
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('admin.pelanggan.index');
});

/*
|--------------------------------------------------------------------------
| TAILOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'level:tailor'])->prefix('tailor')->name('tailor.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TailorDashboardController::class, 'index'])->name('dashboard');

    // Riwayat & Data Pesanan
    Route::get('/riwayat-pesanan', [RiwayatPesananController::class, 'index'])->name('riwayat.pesanan');
    Route::get('/data-pesanan', [RiwayatPesananController::class, 'dataPesanan'])->name('data.pesanan'); // versi 1
    Route::get('/data-pesanan', [DataPesananController::class, 'index'])->name('data.pesanan');         // versi 2 (dipertahankan)

    // Update status
    Route::put('/update-status/{id}', [DataPesananController::class, 'updateStatus'])->name('update.status');

    // Legacy routes (can:is_tailor)
    Route::middleware('can:is_tailor')->group(function () {
        Route::get('/orders', [DataPesananController::class, 'indexLegacy'])->name('orders.index');
        Route::get('/orders/{orderId}', [DataPesananController::class, 'show'])->name('orders.show');
        Route::post('/orders/{orderId}/production', [DataPesananController::class, 'updateProduction'])->name('orders.production.update');
    });
});

/*
|--------------------------------------------------------------------------
| USER AREA (prefix user.)
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->group(function () {

    // Dashboard Produk User
    Route::get('/dashboard', [UserProductController::class, 'dashboard'])->name('dashboard');

    // Product detail (dua versi dipertahankan)
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show'); // dipakai di Blade
    Route::get('/product/{id}',   [UserProductController::class, 'show'])->name('product.show');

    // Checkout & Payment (alur lengkap seperti asli)
    Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/payment', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/prepare', [PaymentController::class, 'prepare'])->name('payment.prepare');

    // Flow checkout via session
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/prefill', [CheckoutController::class, 'prefill'])->name('checkout.prefill');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/create', [CheckoutController::class, 'create'])->name('checkout.create'); // duplikat disimpan

    // Midtrans (di dalam user)
    Route::post('/midtrans/create-snap-token', [CheckoutController::class,'createSnapToken'])->name('midtrans.create-snap-token');
    Route::get('/midtrans/finish',   [MidtransController::class, 'finish'])->name('midtrans.finish');
    Route::get('/midtrans/unfinish', [MidtransController::class, 'unfinish'])->name('midtrans.unfinish');
    Route::get('/midtrans/error',    [MidtransController::class, 'error'])->name('midtrans.error');

    // User Orders (yang penting)
    Route::middleware(['web','auth'])->group(function () {
        Route::get('/orders/{order}', [UserOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/review',  [UserOrderController::class, 'review'])->name('orders.review');
        Route::get('/konfirmasi',      [UserOrderController::class, 'konfirmasi'])->name('konfirmasi.pesanan');
        Route::post('/pembayaran',     [UserOrderController::class, 'pembayaran'])->name('orders.pay');
        Route::post('/pesanan/simpan', [UserOrderController::class, 'simpan'])->name('orders.save');
    });
});

/*
|--------------------------------------------------------------------------
| KERANJANG
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/keranjang',               [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang/tambah',       [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/update',       [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::post('/keranjang/hapus',        [KeranjangController::class, 'hapus'])->name('keranjang.hapus');
    Route::post('/keranjang/kosongkan',    [KeranjangController::class, 'clear'])->name('keranjang.clear');
    Route::delete('/keranjang/{id}',       [KeranjangController::class, 'remove'])->name('keranjang.remove');
    Route::post('/keranjang/clear',        [KeranjangController::class, 'clear'])->name('keranjang.clear'); // alias POST
    Route::post('/keranjang/checkout',     [KeranjangController::class, 'checkout'])->name('keranjang.checkout');
    Route::get('/keranjang/konfirmasi',    [KeranjangController::class, 'konfirmasi'])->name('keranjang.konfirmasi');

    Route::post('/pembayaran/lanjut',      [KeranjangController::class, 'lanjutPembayaran'])->name('pembayaran.lanjut');
    Route::post('keranjang/payNow',        [KeranjangController::class, 'payNow'])->name('keranjang.payNow');
    Route::post('/keranjang/pay',          [KeranjangController::class, 'payNow'])->name('keranjang.pay')->middleware('auth');
});

// Versi lain yang ada di kode (dipertahankan)
Route::post('/keranjang/pay', [KeranjangController::class, 'pay'])->name('keranjang.pay');

/*
|--------------------------------------------------------------------------
| ORDER CUSTOM (oc.*)
|--------------------------------------------------------------------------
*/
Route::prefix('orders/custom')->name('oc.')->group(function () {
    Route::get('/buat',       [OrderCustomController::class, 'index'])->name('buat');
    Route::get('/konfirmasi', [OrderCustomController::class, 'konfirmasi'])->name('konfirmasi');
    Route::post('/simpan',    [OrderCustomController::class, 'simpan'])->name('simpan');

    // Ajax helper
    Route::get('/pakaian/{jenis}/kain',   [OrderCustomController::class, 'kainByPakaian'])->name('ajax.kain');
    Route::get('/pakaian/{jenis}/ukuran', [OrderCustomController::class, 'ukuranByPakaian'])->name('ajax.ukuran');
    Route::get('/pakaian/{jenis}/image',  [OrderCustomController::class, 'imageByPakaian'])->name('ajax.image');
});

/*
|--------------------------------------------------------------------------
| PESANAN (umum)
|--------------------------------------------------------------------------
*/
// Buat & detail pesanan
Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');
Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.show');

// Notifikasi sukses/pending
Route::get('/pesanan/sukses',  [PesananController::class, 'sukses'])->name('pesanan.sukses');
Route::get('/pesanan/pending', [PesananController::class, 'pending'])->name('pesanan.pending');

/*
|--------------------------------------------------------------------------
| MIDTRANS (global endpoints & webhook)
|--------------------------------------------------------------------------
*/
// Token/payment
Route::post('/user/pay/{order}', [MidtransController::class, 'token'])->name('midtrans.user.token');

// Webhook/callbacks (semua endpoint dari kode dipertahankan)
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');
Route::post('/midtrans/notify',       [MidtransController::class, 'notify'])->name('midtrans.notify');

// Tanpa CSRF (seperti di kode)
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
    ->name('midtrans.notification')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

// Callback result pages
Route::get('/payment/finish',   [MidtransController::class, 'finish'])->name('midtrans.finish');
Route::get('/payment/unfinish', [MidtransController::class, 'unfinish'])->name('midtrans.unfinish');
Route::get('/payment/error',    [MidtransController::class, 'error'])->name('midtrans.error');

// Route lain terkait midtrans di kode
Route::post('/midtrans/webhook', [MidtransController::class, 'handle']);
Route::post('/payment/success', [PaymentController::class, 'handlePaymentSuccess'])->name('payment.success');

/*
|--------------------------------------------------------------------------
| PAYMENTS / SNAP TOKEN (tambahan yang ada di kode)
|--------------------------------------------------------------------------
*/
Route::post('/payments/snap-token/checkout', [CheckoutController::class, 'createSnapToken'])->name('payments.snap-token.checkout');
Route::post('/payments/snap-token/payment',  [PaymentController::class, 'createSnapToken'])->name('payments.snap-token.payment');
Route::post('/create-snap-token',            [MidtransController::class, 'createSnapToken'])->name('midtrans.create-snap-token');

/*
|--------------------------------------------------------------------------
| LEGACY REDIRECTS (dipertahankan)
|--------------------------------------------------------------------------
*/
Route::get('/pesanan-saya',       fn() => redirect()->route('user.orders.index'))->name('legacy.pesanan.saya');
Route::get('/user/order',         fn() => redirect()->route('user.orders.index'))->name('legacy.user.order');
Route::get('/konfirmasi-pesanan', fn() => redirect()->route('user.konfirmasi.pesanan'))->name('legacy.konfirmasi');
Route::get('/user/konfirmasi',    fn() => redirect()->route('user.konfirmasi.pesanan'))->name('legacy.user.konfirmasi');

/*
|--------------------------------------------------------------------------
| LAINNYA (tetap ada di kode)
|--------------------------------------------------------------------------
*/
Route::get('/tentang-kami', [PageController::class, 'tentangKami'])->name('tentangkami');

// Simpan checkout ke database (route yang ada di paling bawah)
Route::post('/checkout/store', [OrderController::class, 'storeToDatabase'])->name('checkout.store');
