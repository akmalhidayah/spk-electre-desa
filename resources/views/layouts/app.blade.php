<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SPK ELECTRE Desa')</title>

    <link rel="stylesheet" href="{{ asset('css/spk.css') }}">
</head>
<body>
    @php
        $user = auth()->user();
        $roleLabel = [
            'admin' => 'Admin / Perangkat Desa',
            'kepala_dusun' => 'Kepala Dusun',
            'kepala_desa' => 'Kepala Desa',
        ][$user->role] ?? 'Pengguna';

        $menus = match ($user->role) {
            'admin' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Data Dusun', 'icon' => 'map', 'href' => route('admin.dusuns.index'), 'active' => request()->routeIs('admin.dusuns.*')],
                ['label' => 'Data Kriteria', 'icon' => 'list', 'href' => '#', 'active' => false],
                ['label' => 'Usulan Pembangunan', 'icon' => 'document', 'href' => '#', 'active' => false],
                ['label' => 'Penilaian Alternatif', 'icon' => 'clipboard', 'href' => '#', 'active' => false],
                ['label' => 'Proses ELECTRE', 'icon' => 'calculator', 'href' => '#', 'active' => false],
                ['label' => 'Hasil Rekomendasi', 'icon' => 'chart', 'href' => '#', 'active' => false],
                ['label' => 'Laporan', 'icon' => 'printer', 'href' => '#', 'active' => false],
            ],
            'kepala_dusun' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('kepala-dusun.dashboard'), 'active' => request()->routeIs('kepala-dusun.dashboard')],
                ['label' => 'Ajukan Usulan', 'icon' => 'document', 'href' => '#', 'active' => false],
                ['label' => 'Riwayat Usulan', 'icon' => 'history', 'href' => '#', 'active' => false],
            ],
            'kepala_desa' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('kepala-desa.dashboard'), 'active' => request()->routeIs('kepala-desa.dashboard')],
                ['label' => 'Hasil Rekomendasi', 'icon' => 'chart', 'href' => '#', 'active' => false],
                ['label' => 'Laporan Keputusan', 'icon' => 'printer', 'href' => '#', 'active' => false],
            ],
            default => [],
        };
    @endphp

    <div class="mobile-overlay" data-sidebar-close></div>

    <div class="app-shell">
        <aside class="sidebar" id="appSidebar" aria-label="Sidebar navigasi">
            <div class="brand">
                <div class="brand-mark">E</div>
                <div>
                    <div class="brand-title">SPK ELECTRE</div>
                    <div class="brand-subtitle">Desa Barambang</div>
                </div>
                <button class="icon-button sidebar-close" type="button" data-sidebar-close aria-label="Tutup menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                @foreach ($menus as $menu)
                    <a
                        href="{{ $menu['href'] }}"
                        class="sidebar-link {{ $menu['active'] ? 'active' : '' }}"
                    >
                        <span class="sidebar-icon">
                            @switch($menu['icon'])
                                @case('map')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg>
                                    @break
                                @case('list')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" /><path d="M3 6h.01M3 12h.01M3 18h.01" /></svg>
                                    @break
                                @case('document')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" /></svg>
                                    @break
                                @case('clipboard')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5h6M9 5a3 3 0 0 1 6 0M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" /><path d="M9 13h6M9 17h4" /></svg>
                                    @break
                                @case('calculator')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01M8 19h.01M12 19h.01M16 19h.01" /></svg>
                                    @break
                                @case('chart')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg>
                                    @break
                                @case('printer')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                                    @break
                                @case('history')
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 4v6h6" /><path d="M12 7v5l3 2" /></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1Z" /></svg>
                            @endswitch
                        </span>
                        <span>{{ $menu['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ $roleLabel }}</div>
                </div>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <div class="topbar-inner">
                    <button class="icon-button hamburger" type="button" data-sidebar-open aria-label="Buka menu">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="topbar-title">
                        <div class="eyebrow">@yield('eyebrow', 'Dashboard')</div>
                        <h1 class="page-title">@yield('page-title')</h1>
                    </div>

                    <div class="topbar-actions">
                        <div class="topbar-user">
                            <span>{{ $user->name }}</span>
                            <small>{{ $roleLabel }}</small>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline">
                                <svg class="btn-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5" /><path d="M15 12H3" /><path d="M21 4v16" /></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="content fade-in">
                @if (session('status'))
                    <div class="alert alert-success js-inline-alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger js-inline-alert">
                        Terdapat {{ $errors->count() }} kesalahan input. Silakan periksa kembali form.
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var body = document.body;
            var sidebar = document.getElementById('appSidebar');
            var openButtons = document.querySelectorAll('[data-sidebar-open]');
            var closeButtons = document.querySelectorAll('[data-sidebar-close]');

            openButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    body.classList.add('sidebar-open');
                });
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    body.classList.remove('sidebar-open');
                });
            });

            if (sidebar) {
                sidebar.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        body.classList.remove('sidebar-open');
                    });
                });
            }

            var successMessage = @json(session('success'));
            var errorMessage = @json(session('error'));
            var validationMessage = @json($errors->any() ? 'Terdapat kesalahan validasi. Silakan periksa kembali input Anda.' : null);

            function fallbackAlert(message) {
                if (message) {
                    window.alert(message);
                }
            }

            if (window.Swal) {
                if (successMessage) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: successMessage,
                        showConfirmButton: false,
                        timer: 2600,
                        timerProgressBar: true
                    });
                }

                if (errorMessage) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: errorMessage,
                        confirmButtonColor: '#047857'
                    });
                }

                if (validationMessage && !errorMessage) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Belum Sesuai',
                        text: validationMessage,
                        confirmButtonColor: '#047857'
                    });
                }
            } else {
                fallbackAlert(successMessage || errorMessage || validationMessage);
            }

            document.querySelectorAll('form.js-confirm').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }

                    var title = form.dataset.title || 'Konfirmasi aksi';
                    var text = form.dataset.text || 'Apakah Anda yakin ingin melanjutkan?';
                    var icon = form.dataset.icon || 'warning';
                    var confirmButton = form.dataset.confirmButton || 'Ya, lanjutkan';

                    if (!window.Swal) {
                        if (!window.confirm(text)) {
                            event.preventDefault();
                        }

                        return;
                    }

                    event.preventDefault();

                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonText: confirmButton,
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#047857',
                        cancelButtonColor: '#64748b'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
