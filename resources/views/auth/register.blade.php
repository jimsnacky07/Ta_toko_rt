<!-- resources/views/auth/register.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Akun - Romansa Tailor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .custom-box {
            border: 1px solid #ccc;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
        }
        .custom-btn {
            background-color: #007bff;
            color: #fff;
        }
        .custom-btn:hover {
            background-color: #0069d9;
        }
        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="custom-box shadow-sm">
            <h4 class="fw-bold">Selamat Datang Pengguna Baru!</h4>
            <p class="text-muted mb-4">Buat Akun Baru</p>

           <form action="{{ url('/register') }}" method="POST">
        @csrf

        <label for="email">Email:</label><br />
        <input type="email" id="email" name="email" value="{{ old('email') }}" required /><br /><br />

        <label for="password">Password:</label><br />
        <input type="password" id="password" name="password" required /><br /><br />

        <label for="nama">Nama:</label><br />
        <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required /><br /><br />

        <label for="no_telp">No Telp:</label><br />
        <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required /><br /><br />

        <label for="alamat">Alamat:</label><br />
        <textarea id="alamat" name="alamat" required>{{ old('alamat') }}</textarea><br /><br />


        <button type="submit">Register</button>
    </form>

        </div>
    </div>
</body>
</html>
