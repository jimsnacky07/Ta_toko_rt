<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\MidtransController;
use App\Http\Controllers\Admin\GaleriJahitController;
use App\Http\Controllers\User\ProductController as UserProductController;
use App\Http\Controllers\Tailor\DashboardController as TailorDashboardController;
use App\Http\Controllers\Tailor\RiwayatPesananController;
use App\Http\Controllers\Tailor\DataPesananController;
use App\Http\Controllers\User\KeranjangController;
use App\Http\Controllers\User\OrderCustomController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
Route::get('/register', [App\Http\Controllers\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\RegisterController::class, 'register']);

// Lupa Password
Route::get('/forgot-password', fn() => view('auth.forgot-password'))
    ->middleware('guest')
    ->name('password.request');

// Buat pesanan 
// Route untuk membuat user admin dan tailor secara otomatis
Route::get('/setup-users', function () {
    // Cek apakah user admin sudah ada
    $admin = User::where('email', 'admin@contoh.com')->first();
    if (!$admin) {
        User::create([
            'nama' => 'Admin',
            'email' => 'admin@contoh.com',
            'password' => Hash::make('zxcvbnm11'),
            'level' => 'admin',
            'no_telp' => '08123456789',
            'alamat' => 'Alamat admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $adminMsg = "Admin berhasil dibuat!<br>";
    } else {
        $adminMsg = "Admin sudah ada.<br>";
    }

    // Cek apakah user tailor sudah ada
    $tailor = User::where('email', 'tailor@contoh.com')->first();
    if (!$tailor) {
        User::create([
            'nama' => 'Tailor',
            'email' => 'tailor@contoh.com',
            'password' => Hash::make('zxcvbnm11'),
            'level' => 'tailor',
            'no_telp' => '08123456780',
            'alamat' => 'Alamat tailor',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $tailorMsg = "Tailor berhasil dibuat!<br>";
    } else {
        $tailorMsg = "Tailor sudah ada.<br>";
    }

    return $adminMsg . $tailorMsg . "<br>Silakan login dengan:<br>"
        . "Admin: admin@contoh.com / zxcvbnm11<br>"
        . "Tailor: tailor@contoh.com / zxcvbnm11";
});

Route::get('/buat-pesanan', function () {
    return view('user.pesanan.order_custom');
})->name('buat.pesanan');


// Keranjang (user)
Route::middleware(['auth'])->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::post('/keranjang/tambah', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/update', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::post('/keranjang/hapus', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');
    Route::post('/keranjang/clear', [KeranjangController::class, 'clear'])->name('keranjang.clear');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'remove'])->name('keranjang.remove');
    Route::post('/keranjang/checkout', [KeranjangController::class, 'checkout'])->name('keranjang.checkout');
    Route::get('/keranjang/konfirmasi', [KeranjangController::class, 'konfirmasi'])->name('keranjang.konfirmasi');
    Route::post('/pembayaran/lanjut', [KeranjangController::class, 'lanjutPembayaran'])->name('pembayaran.lanjut');
    Route::post('/keranjang/pay', [KeranjangController::class, 'pay'])->name('keranjang.pay');
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
    Route::get('/dashboard', function () {
        try {
            // Pastikan ada produk, jika tidak jalankan seeder
            $productCount = \App\Models\Product::count();
            if ($productCount == 0) {
                Artisan::call('db:seed', ['--class' => 'PoductSeeder']);
            }

            $products = \App\Models\Product::all();
            return view('user.dashboard', compact('products'));
        } catch (Exception $e) {
            // Jika tabel products belum ada, tampilkan pesan setup
            return redirect('/setup-all')->with('error', 'Database belum disetup. Silakan jalankan setup terlebih dahulu.');
        }
    })->name('dashboard');

    // Debug route untuk cek produk
    Route::get('/debug-products', function () {
        try {
            $products = \App\Models\Product::all();

            $output = '<h1>Debug Products</h1>';
            $output .= '<p>Total products: ' . $products->count() . '</p>';

            if ($products->isNotEmpty()) {
                $output .= '<ul>';
                foreach ($products as $product) {
                    $output .= "<li>{$product->name} - Rp " . number_format($product->price) . " - {$product->kategory}</li>";
                }
                $output .= "</ul>";
            } else {
                $output .= "<p style='color: red;'>No products found in database!</p>";
            }

            return $output;
        } catch (Exception $e) {
            return '<h1>Error</h1><p>' . $e->getMessage() . '</p>';
        }
    });

    // Route untuk debug orders
    Route::get('/debug-orders', function () {
        $orders = \App\Models\Order::with('user')->get();
        $users = \App\Models\User::all();

        $output = '<h1>Debug Orders & Users</h1>';
        $output .= '<p>Total orders: ' . $orders->count() . '</p>';
        $output .= '<p>Total users: ' . $users->count() . '</p>';

        $output .= '<h2>Orders:</h2>';
        if ($orders->isNotEmpty()) {
            $output .= '<ul>';
            foreach ($orders as $order) {
                $userName = $order->user->nama ?? $order->user->name ?? 'Unknown';
                $output .= "<li>Order #{$order->id} - {$userName} - Status: {$order->status} - Total: Rp " . number_format($order->total_amount) . "</li>";
            }
            $output .= "</ul>";
        } else {
            $output .= "<p style='color: red;'>No orders found!</p>";
        }

        $output .= '<h2>Users:</h2>';
        $output .= '<ul>';
        foreach ($users as $user) {
            $output .= "<li>#{$user->id} - {$user->nama} ({$user->email}) - Level: {$user->level}</li>";
        }
        $output .= "</ul>";

        return $output;
    });

    // Route untuk setup database dan produk
    Route::get('/setup-all', function () {
        try {
            // Jalankan migrasi
            Artisan::call('migrate');

            // Update semua produk dengan ukuran standar
            DB::table('products')->update([
                'sizes' => 'S,M,L,XL',
                'ukuran' => 'S, M, L, XL',
                'deskripsi_ukuran' => 'S: Lingkar dada 88-92cm, M: Lingkar dada 92-96cm, L: Lingkar dada 96-100cm, XL: Lingkar dada 100-104cm'
            ]);

            // Jalankan seeder
            Artisan::call('db:seed', ['--class' => 'ProductSeeder']);
            Artisan::call('db:seed', ['--class' => 'AdminUserSeeder']);
            Artisan::call('db:seed', ['--class' => 'TailorSeeder']);

            // Pastikan ada produk di database
            $productCount = \App\Models\Product::count();
            if ($productCount == 0) {
                // Jika tidak ada produk, jalankan seeder lagi
                Artisan::call('db:seed', ['--class' => 'ProductSeeder', '--force' => true]);
            }

            // Buat sample order untuk testing
            $user = \App\Models\User::where('level', 'customer')->first();
            if ($user && \App\Models\Product::count() > 0) {
                $firstProduct = \App\Models\Product::first();
                $order = \App\Models\Order::create([
                    'user_id' => $user->id,
                    'kode_pesanan' => 'ORD-' . now()->format('YmdHis'),
                    'order_code' => 'ORD-' . now()->format('YmdHis'),
                    'status' => 'menunggu',
                    'total_harga' => $firstProduct->price,
                    'total_amount' => $firstProduct->price,
                    'metode_pembayaran' => 'midtrans',
                ]);

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $firstProduct->id,
                    'product_name' => $firstProduct->name,
                    'price' => $firstProduct->price,
                    'quantity' => 1,
                    'total_price' => $firstProduct->price,
                    'size' => 'M',
                    'color' => 'Biru Tua',
                    'status' => 'menunggu'
                ]);
            }

            $productCount = \App\Models\Product::count();
            $userCount = \App\Models\User::count();
            $orderCount = \App\Models\Order::count();

            return "<h1>🎉 Setup Complete!</h1>" .
                "<p>✅ Database: All tables created successfully</p>" .
                "<p>✅ Products: {$productCount} products with sizes S,M,L,XL</p>" .
                "<p>✅ Users: {$userCount} users (admin, tailor, customer)</p>" .
                "<p>✅ Orders: {$orderCount} sample orders created</p>" .
                "<hr style='margin: 20px 0;'>" .
                "<h2>🔐 Login Credentials:</h2>" .
                "<p><strong>Admin:</strong> admin@admin.com / zxcvbnm123</p>" .
                "<p><strong>Tailor:</strong> siti@tailor.com / zxcvbnm123</p>" .
                "<p><strong>Customer:</strong> user@user.com / zxcvbnm123</p>" .
                "<hr style='margin: 20px 0;'>" .
                "<h2>🚀 Access Links:</h2>" .
                "<p><a href='/login' style='color: green; text-decoration: underline; font-size: 18px;'>🔑 Login Page</a></p>" .
                "<p><a href='/dashboard' style='color: blue; text-decoration: underline;'>👤 User Dashboard</a></p>" .
                "<p><a href='/admin/dashboard' style='color: purple; text-decoration: underline;'>👨‍💼 Admin Dashboard</a></p>" .
                "<p><a href='/tailor/dashboard' style='color: orange; text-decoration: underline;'>✂️ Tailor Dashboard</a></p>" .
                "<p><a href='/debug-orders' style='color: gray; text-decoration: underline;'>🔍 Debug Orders</a></p>";
        } catch (Exception $e) {
            return "<h1>Setup Error!</h1><p style='color: red;'>" . $e->getMessage() . "</p>";
        }
    });

    // Profil
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil/update', [ProfilController::class, 'update'])->name('profil.update');

    // Test login admin
    Route::get('/test-admin-login', function () {
        try {
            // Cari user admin
            $admin = \App\Models\User::where('level', 'admin')->first();
            if (!$admin) {
                return 'Admin user tidak ditemukan di database!';
            }

            // Login sebagai admin
            Auth::login($admin);

            return redirect('/admin/dashboard')->with('success', 'Login admin berhasil!');
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    // Route untuk mengecek isi tabel users
    Route::get('/check-users', function () {
        try {
            $users = DB::table('users')->get();

            $output = '<h1>Daftar User</h1>';
            $output .= '<p>Total user: ' . $users->count() . '</p>';

            if ($users->isNotEmpty()) {
                $output .= '<table border="1">';
                $output .= '<tr><th>ID</th><th>Nama</th><th>Email</th><th>Level</th></tr>';

                foreach ($users as $user) {
                    $output .= sprintf(
                        '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                        $user->id,
                        htmlspecialchars($user->nama ?? 'N/A'),
                        htmlspecialchars($user->email),
                        $user->level
                    );
                }

                $output .= '</table>';
            } else {
                $output .= '<p>Tidak ada data user yang ditemukan.</p>';
            }

            // Cek koneksi database
            $output .= '<h2>Info Koneksi Database</h2>';
            try {
                $pdo = DB::connection()->getPdo();
                $output .= '<p>Koneksi database berhasil: ' . $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS) . '</p>';
                $output .= '<p>Database: ' . DB::connection()->getDatabaseName() . '</p>';
            } catch (\Exception $e) {
                $output .= '<p>Error koneksi database: ' . $e->getMessage() . '</p>';
            }

            return $output;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    // Pusat Pesanan (alias)
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pusat.pesanan');
    Route::get('/user/orders', [PesananController::class, 'index'])->name('user.orders.index');

    // Route untuk mengecek data user dan pesanan
    Route::get('/check-data', function () {
        try {
            $output = "<h1>Data User dan Pesanan</h1>";

            // Cek user yang sedang login
            $user = Auth::user();
            if (!$user) {
                return 'Tidak ada user yang login. <a href="' . route('login') . '">Login dulu</a>';
            }

            $output .= "<h2>User yang Login</h2>";
            $output .= "<pre>" . print_r($user->toArray(), true) . "</pre>";

            // Cek semua user di database
            $users = \App\Models\User::all();
            $output .= "<h2>Semua User di Database (Total: " . $users->count() . ")</h2>";
            $output .= "<pre>" . print_r($users->toArray(), true) . "</pre>";

            // Cek pesanan user ini
            $orders = \App\Models\Order::where('user_id', $user->id)->get();
            $output .= "<h2>Pesanan User (Total: " . $orders->count() . ")</h2>";
            $output .= "<pre>" . print_r($orders->toArray(), true) . "</pre>";

            // Cek semua pesanan di database
            $allOrders = \App\Models\Order::all();
            $output .= "<h2>Semua Pesanan di Database (Total: " . $allOrders->count() . ")</h2>";
            $output .= "<pre>" . print_r($allOrders->toArray(), true) . "</pre>";

            return $output;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    // Test route to create order manually for current user
    Route::get('/test-create-order', function () {
        try {
            $user = Auth::user();
            if (!$user) {
                return 'Silakan login terlebih dahulu';
            }

            // Buat order baru
            $order = new \App\Models\Order([
                'user_id' => $user->id,
                'order_code' => 'TEST-' . time() . '-' . rand(1000, 9999),
                'status' => 'completed',
                'total_amount' => 100000,
                'paid_at' => now(),
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '081234567890',
                'shipping_address' => 'Alamat pengiriman test',
                'payment_status' => 'paid'
            ]);

            $order->save();

            // Buat order item
            $orderItem = new \App\Models\OrderItem([
                'order_id' => $order->id,
                'product_id' => 1,
                'name' => 'Produk Test',
                'price' => 100000,
                'quantity' => 1,
                'total_price' => 100000,
                'garment_type' => 'Kemeja',
                'fabric_type' => 'Katun',
                'size' => 'L'
            ]);

            $orderItem->save();

            return redirect()->route('pusat.pesanan')
                ->with('success', 'Order test berhasil dibuat! Kode: ' . $order->order_code);
        } catch (\Exception $e) {
            return 'Terjadi kesalahan: ' . $e->getMessage();
        }
    });

    // Test route to create sample orders for admin testing
    Route::get('/create-sample-orders', function () {
        try {
            // Create sample customer if not exists
            $customer = \App\Models\User::firstOrCreate(
                ['email' => 'customer@test.com'],
                [
                    'nama' => 'Customer Test',
                    'password' => Hash::make('password'),
                    'level' => 'user',
                    'no_telp' => '081234567890',
                    'alamat' => 'Alamat test customer'
                ]
            );

            // Create sample product order
            $productOrder = \App\Models\Order::create([
                'user_id' => $customer->id,
                'order_code' => 'PROD-' . time() . '-' . rand(100, 999),
                'status' => 'paid',
                'total_amount' => 150000,
                'paid_at' => now(),
            ]);

            \App\Models\OrderItem::create([
                'order_id' => $productOrder->id,
                'product_id' => 1, // Assuming product exists
                'garment_type' => 'Kemeja Formal',
                'fabric_type' => 'Katun Premium',
                'size' => 'L',
                'price' => 150000,
                'quantity' => 1,
                'total_price' => 150000,
                'special_request' => 'Warna biru navy'
            ]);

            // Create sample custom order
            $customOrder = \App\Models\Order::create([
                'user_id' => $customer->id,
                'order_code' => 'CUSTOM-' . time() . '-' . rand(100, 999),
                'status' => 'menunggu',
                'total_amount' => 300000,
                'paid_at' => now(),
            ]);

            \App\Models\OrderItem::create([
                'order_id' => $customOrder->id,
                'product_id' => null,
                'garment_type' => 'Order Custom - Jas',
                'fabric_type' => 'Wool Premium',
                'size' => 'Custom (Chest: 100cm, Length: 70cm)',
                'price' => 300000,
                'quantity' => 1,
                'total_price' => 300000,
                'special_request' => 'Jas formal untuk acara pernikahan, warna hitam dengan detail emas'
            ]);

            // Create another custom order
            $customOrder2 = \App\Models\Order::create([
                'user_id' => $customer->id,
                'order_code' => 'CUSTOM-' . time() . '-' . rand(100, 999),
                'status' => 'diproses',
                'total_amount' => 200000,
                'paid_at' => now(),
            ]);

            \App\Models\OrderItem::create([
                'order_id' => $customOrder2->id,
                'product_id' => null,
                'garment_type' => 'Order Custom - Dress',
                'fabric_type' => 'Satin',
                'size' => 'Custom (Bust: 85cm, Waist: 70cm)',
                'price' => 200000,
                'quantity' => 1,
                'total_price' => 200000,
                'special_request' => 'Dress pesta warna merah dengan aplikasi payet'
            ]);

            return redirect('/admin/daftar-pesanan')->with('ok', 'Sample orders berhasil dibuat! Silakan cek di dashboard admin.');
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    // Test route to create Order Custom manually
    Route::get('/test-create-custom-order', function () {
        try {
            $user = Auth::user();
            if (!$user) {
                return 'Please login first to create test order.';
            }

            $order = \App\Models\Order::create([
                'user_id' => $user->id,
                'order_code' => 'CUSTOM-' . time() . '-' . rand(100, 999),
                'status' => 'paid',
                'total_amount' => 256000,
                'paid_at' => now(),
            ]);

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => null,
                'garment_type' => 'Order Custom - Blazer',
                'fabric_type' => 'American Drill',
                'size' => 'Custom Size',
                'price' => 256000,
                'quantity' => 1,
                'total_price' => 256000,
                'special_request' => 'Custom Order - Blazer American Drill untuk ' . ($user->nama ?? $user->name)
            ]);

            return redirect('/admin/daftar-pesanan')->with('ok', "Order Custom test berhasil dibuat! Kode: {$order->order_code}");
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    // Debug route to see orders data with authentication
    Route::get('/debug-orders', function () {
        $output = "=== DEBUG ORDERS ===\n\n";

        // Get current user info
        $user = Auth::user();
        if (!$user) {
            return 'Please login first';
        }

        $output .= "User Info:\n";
        $output .= "- ID: " . $user->id . "\n";
        $output .= "- Name: " . ($user->name ?? $user->nama) . "\n";
        $output .= "- Email: " . $user->email . "\n\n";

        // Check database connection
        try {
            $dbConnected = DB::connection()->getPdo();
            $output .= "Database: CONNECTED\n";

            // Check if tables exist
            $tables = ['orders', 'order_items', 'users'];
            foreach ($tables as $table) {
                $exists = Schema::hasTable($table) ? 'EXISTS' : 'MISSING';
                $output .= "- Table {$table}: {$exists}\n";
            }
            $output .= "\n";

            // Get user from database by email
            $dbUser = \App\Models\User::where('email', $user->email)->first();
            if (!$dbUser) {
                $output .= "ERROR: User not found in database by email: " . $user->email . "\n";
            } else {
                $output .= "Database User Info:\n";
                $output .= "- ID: " . $dbUser->id . "\n";
                $output .= "- Name: " . ($dbUser->name ?? $dbUser->nama) . "\n";
                $output .= "- Email: " . $dbUser->email . "\n\n";

                // Get user's orders
                $orders = \App\Models\Order::where('user_id', $dbUser->id)
                    ->with('orderItems')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $output .= "User Orders (" . $orders->count() . "):\n";
                foreach ($orders as $order) {
                    $output .= "- Order #" . $order->order_code . " | " . $order->status . " | " . $order->total_amount . " | " . $order->created_at . "\n";
                    foreach ($order->orderItems as $item) {
                        $output .= "  - " . $item->garment_type . " (x" . $item->quantity . ") = " . $item->total_price . "\n";
                    }
                }
                $output .= "\n";

                // Get all orders for debugging
                $allOrders = \App\Models\Order::with('orderItems')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $output .= "All Orders (" . $allOrders->count() . "):\n";
                foreach ($allOrders as $order) {
                    $user = \App\Models\User::find($order->user_id);
                    $output .= "- Order #" . $order->order_code . " | " .
                        "User: " . ($user ? $user->email : 'Unknown') . " | " .
                        $order->status . " | " . $order->total_amount . " | " . $order->created_at . "\n";
                }
            }

            // Check session data
            $output .= "\nSession Data:\n";
            $output .= "- Session ID: " . session()->getId() . "\n";
            $output .= "- Pending Order: " . (session('pending_order') ? 'EXISTS' : 'NOT SET') . "\n";

            // Log session data for debugging (be careful with sensitive data in production)
            $sessionData = session()->all();
            unset($sessionData['_token']); // Remove CSRF token
            $output .= "- Session Contents: " . json_encode($sessionData, JSON_PRETTY_PRINT) . "\n";
        } catch (\Exception $e) {
            $output .= "ERROR: " . $e->getMessage() . "\n";
            $output .= "Trace: " . $e->getTraceAsString() . "\n";
        }

        return response()->json([
            'auth_user' => [
                'id' => $user->id,
                'name' => $user->name ?? $user->nama,
                'email' => $user->email
            ],
            'db_user' => [
                'id' => $dbUser->id,
                'nama' => $dbUser->nama,
                'email' => $dbUser->email
            ],
            'user_orders_count' => $orders->count(),
            'user_orders' => $orders->toArray(),
            'all_orders_count' => $allOrders->count(),
            'all_orders' => $allOrders->toArray(),
            'session_pending_order' => session('pending_order')
        ]);
    })->middleware('auth');

    // Detailed debug route
    Route::get('/debug-orders-detailed', function () {
        try {
            $user = Auth::user();
            if (!$user) {
                return 'Please login first. Current auth status: ' . (Auth::check() ? 'Logged in' : 'Not logged in');
            }

            $output = "=== DEBUG ORDERS ===\n";
            $output .= "User: " . $user->name . " (ID: " . $user->id . ")\n";
            $output .= "Time: " . now() . "\n\n";

            // Check if tables exist
            $ordersTableExists = Schema::hasTable('orders');
            $orderItemsTableExists = Schema::hasTable('order_items');

            $output .= "Tables Status:\n";
            $output .= "- orders: " . ($ordersTableExists ? 'EXISTS' : 'NOT EXISTS') . "\n";
            $output .= "- order_items: " . ($orderItemsTableExists ? 'EXISTS' : 'NOT EXISTS') . "\n\n";

            if ($ordersTableExists) {
                $userOrders = \App\Models\Order::where('user_id', $user->id)->get();
                $allOrders = \App\Models\Order::all();

                $output .= "Orders Count:\n";
                $output .= "- User orders: " . $userOrders->count() . "\n";
                $output .= "- All orders: " . $allOrders->count() . "\n\n";

                if ($userOrders->count() > 0) {
                    $output .= "User Orders:\n";
                    foreach ($userOrders as $order) {
                        $output .= "- Order #" . $order->order_code . " | Status: " . $order->status . " | Amount: " . $order->total_amount . "\n";
                    }
                }
            }

            return response($output)->header('Content-Type', 'text/plain');
        } catch (Exception $e) {
            return 'ERROR: ' . $e->getMessage() . "\n\nTrace: " . $e->getTraceAsString();
        }
    })->middleware('auth');
});

/*
|--------------------------------------------------------------------------
| TAILOR ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('tailor')->middleware(['auth', 'level:tailor'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\TailorController::class, 'dashboard'])->name('tailor.dashboard');
    Route::get('/orders', [\App\Http\Controllers\TailorController::class, 'orders'])->name('tailor.orders');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\TailorController::class, 'updateOrderStatus'])->name('tailor.orders.status');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', 'level:admin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/daftar-pesanan', [\App\Http\Controllers\AdminController::class, 'daftarPesanan'])->name('admin.daftar.pesanan');
    Route::get('/pesanan', [\App\Http\Controllers\AdminController::class, 'daftarPesanan'])->name('admin.daftar-pesanan'); // alias

    // Order Management
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'showOrder'])->name('admin.orders.show');
    Route::get('/orders/{order}/details', [\App\Http\Controllers\Admin\OrderController::class, 'getOrderDetails'])->name('admin.orders.details');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::post('/orders/{orderId}/assign', [\App\Http\Controllers\Admin\OrderController::class, 'assign'])->name('admin.orders.assign');

    // Pelanggan Management
    Route::get('/pelanggan', [\App\Http\Controllers\Admin\PelangganController::class, 'index'])->name('admin.pelanggan');
    Route::get('/pelanggan/{id}', [\App\Http\Controllers\Admin\PelangganController::class, 'show'])->name('admin.pelanggan.show');
    Route::delete('/pelanggan/{id}', [\App\Http\Controllers\Admin\PelangganController::class, 'destroy'])->name('admin.pelanggan.destroy');

    // User Management
    Route::resource('users', 'App\Http\Controllers\UserController')->except(['show']);
    Route::get('users/{user}/delete', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Galeri Jahit (resource + custom names)
    Route::resource('galeri-jahit', GaleriJahitController::class)
        ->parameters(['galeri-jahit' => 'product'])
        ->names([
            'index'   => 'admin.galeri.jahit.index',
            'create'  => 'admin.galeri.jahit.create',
            'store'   => 'admin.galeri.jahit.store',
            'show'    => 'admin.galeri.jahit.show',
            'edit'    => 'admin.galeri.jahit.edit',
            'update'  => 'admin.galeri.jahit.update',
            'destroy' => 'admin.galeri.jahit.destroy',
        ]);

    // Products
    Route::get('/products', function () {
        try {
            $products = \App\Models\Product::paginate(12);
            return view('user.products.index', compact('products'));
        } catch (Exception $e) {
            return redirect('/setup-all')->with('error', 'Database belum disetup. Silakan jalankan setup terlebih dahulu.');
        }
    })->name('user.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('user.products.show');
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
    Route::get('/data-pesanan', [DataPesananController::class, 'index'])->name('data.pesanan');

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


    // Checkout & Payment (user)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::get('/payment', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/prepare', [PaymentController::class, 'prepare'])->name('payment.prepare');
    // Midtrans (user)
    Route::post('/midtrans/create-snap-token', [CheckoutController::class, 'createSnapToken'])->name('user.midtrans.create-snap-token');
    Route::get('/midtrans/finish', [MidtransController::class, 'finish'])->name('user.midtrans.finish');
    Route::get('/midtrans/unfinish', [MidtransController::class, 'unfinish'])->name('user.midtrans.unfinish');
    Route::get('/midtrans/error', [MidtransController::class, 'error'])->name('user.midtrans.error');

    // User Orders (yang penting)
    Route::middleware(['web', 'auth'])->group(function () {
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
    Route::post('/keranjang/clear',        [KeranjangController::class, 'clear'])->name('keranjang.clear');
    Route::post('/keranjang/checkout',     [KeranjangController::class, 'checkout'])->name('keranjang.checkout');
    Route::get('/keranjang/konfirmasi',    [KeranjangController::class, 'konfirmasi'])->name('keranjang.konfirmasi');

    Route::post('/pembayaran/lanjut',      [KeranjangController::class, 'lanjutPembayaran'])->name('pembayaran.lanjut');
    Route::post('/keranjang/pay',          [KeranjangController::class, 'pay'])->name('keranjang.pay');
});

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

Route::post('/pesanan/store', [PesananController::class, 'store'])->name('pesanan.store');
Route::get('/pesanan/{id}', [PesananController::class, 'show'])->name('pesanan.show');

Route::get('/pesanan/sukses',  [PesananController::class, 'sukses'])->name('pesanan.sukses');
Route::get('/pesanan/pending', [PesananController::class, 'pending'])->name('pesanan.pending');

/*
|--------------------------------------------------------------------------
| MIDTRANS (global endpoints & webhook)
|--------------------------------------------------------------------------
*/
// Token/payment
Route::post('/user/pay/{order}', [MidtransController::class, 'token'])->name('midtrans.user.token');

// Webhook/callbacks (HANYA SATU ENDPOINT UNTUK WEBHOOK)
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
    ->name('midtrans.notification')
    ->withoutMiddleware([
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);

// Ping endpoint to verify webhook reachability without any web middleware
Route::post('/midtrans/ping', function () {
    Log::info('=== [MIDTRANS] PING RECEIVED ===', [
        'headers' => request()->headers->all(),
        'body' => request()->all(),
    ]);
    return response()->json(['ok' => true]);
})->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

// Callback result pages (global - untuk webhook)
Route::get('/payment/finish',   [MidtransController::class, 'finish'])->name('payment.finish');
Route::get('/payment/unfinish', [MidtransController::class, 'unfinish'])->name('payment.unfinish');
Route::get('/payment/error',    [MidtransController::class, 'error'])->name('payment.error');

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


// Route debug/test hanya aktif di local
if (app()->environment('local')) {
    Route::get('/debug-orders', function () {
        // ...existing debug code...
    });
    Route::get('/setup-all', function () {
        // ...existing setup code...
    });
    // Tambahkan route debug/test lain di sini jika perlu
}
