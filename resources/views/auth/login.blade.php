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
    <main class="auth-page auth-page-polished">
        <section class="auth-hero">
            <div class="auth-hero-mark">SPK</div>
            <div>
                <div class="auth-kicker">Desa Barambang</div>
                <h1>SPK Prioritas Pembangunan Desa</h1>
                <p>Metode ELECTRE untuk rekomendasi prioritas pembangunan antar dusun secara terukur, transparan, dan siap dipertanggungjawabkan.</p>
            </div>
            <div class="auth-hero-grid" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>
        </section>

        <section class="auth-card auth-card-polished">
            <div class="auth-header">
                <div class="auth-kicker">Masuk Sistem</div>
                <h2 class="auth-title">Selamat Datang</h2>
                <p class="auth-subtitle">Gunakan akun sesuai peran pengguna.</p>
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

            <form method="POST" action="{{ route('login.process') }}" class="form-stack">
                @csrf

                <div class="form-group input-with-icon">
                    <label for="email" class="form-label">Email</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4Z" /><path d="m4 7 8 6 8-6" /></svg>
                    </span>
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

                <div class="form-group input-with-icon">
                    <label for="password" class="form-label">Password</label>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" /><path d="M5 11h14v10H5Z" /></svg>
                    </span>
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

                <button type="submit" class="btn btn-primary" data-loading-text="Masuk...">
                    Masuk
                </button>
            </form>

            <div class="demo-box">
                <strong>Akun Demo</strong>
                <span>Admin: admin@example.com</span>
                <span>Kepala Desa: kepaladesa@example.com</span>
                <span>Kepala Dusun: katute@example.com</span>
                <span>Password: password</span>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    button.disabled = true;
                    button.textContent = button.dataset.loadingText || 'Memproses...';
                });
            });
        });
    </script>
</body>
</html>
