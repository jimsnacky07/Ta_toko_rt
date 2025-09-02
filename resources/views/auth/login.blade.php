<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Romansa Tailor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f5f5;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
        }

        .left-panel {
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
            background-size: cover;
            background-position: center;
        }

        .left-panel h1 {
            font-weight: bold;
        }

        .right-panel {
            background-color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.05);
        }

        .form-box {
            width: 100%;
            max-width: 400px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="container-fluid login-container">
        {{-- Left Panel --}}
        <div class="col-md-6 left-panel" style="background-image: url('{{ asset('images/login.jpg') }}');">
            <div>
                <h1>Romansa Tailor</h1>
                <p>Melayani Jasa Jahit dan vermak pakaian</p>
                <a href="{{ route('landinghome') }}" class="btn btn-light mt-3">Home</a>
            </div>
        </div>

        {{-- Right Panel --}}
        <div class="col-md-6 right-panel">
            <div class="form-box">
                {{-- Logo --}}
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logo romansa.png') }}" alt="Romansa Tailor Logo" class="mb-1 img-fluid" style="max-width: 180px;">
                    <h4 class="mt-1">Selamat Datang!</h4>
                </div>

                {{-- Display Success Message --}}
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Display Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="/login">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('register') }}">Belum memiliki akun?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
