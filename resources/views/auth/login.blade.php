<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - SPK ELECTRE Desa</title>

    <link rel="stylesheet" href="{{ asset('css/spk.css') }}">
</head>
<body>
    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-kicker">Desa Barambang</div>
                <h1 class="auth-title">SPK Prioritas Pembangunan Desa</h1>
                <p class="auth-subtitle">Metode ELECTRE</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="form-control"
                        placeholder="admin@example.com"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="form-control"
                        placeholder="Masukkan password"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Masuk
                </button>
            </form>
        </div>
    </main>
</body>
</html>
