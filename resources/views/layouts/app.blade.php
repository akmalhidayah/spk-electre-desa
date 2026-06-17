<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SPK ELECTRE Desa')</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ filemtime(public_path('favicon.png')) }}">
    <link rel="stylesheet" href="{{ asset('css/spk.css') }}?v={{ filemtime(public_path('css/spk.css')) }}">
</head>
<body>
    @php
        $user = auth()->user();
        $roleLabel = [
            'admin' => 'Admin / Perangkat Desa',
            'kepala_dusun' => 'Kepala Dusun',
            'kepala_desa' => 'Kepala Desa',
        ][$user->role] ?? 'Pengguna';

        $roleBadgeClass = [
            'admin' => 'badge-info',
            'kepala_dusun' => 'badge-warning',
            'kepala_desa' => 'badge-success',
        ][$user->role] ?? 'badge-muted';

        $menus = match ($user->role) {
            'admin' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Welcome Desa', 'icon' => 'home', 'href' => route('admin.welcome-desa.index'), 'active' => request()->routeIs('admin.welcome-desa.*')],
                ['label' => 'Usulan Pembangunan', 'icon' => 'document', 'href' => route('admin.usulan.index'), 'active' => request()->routeIs('admin.usulan.*')],
                ['label' => 'Penilaian Alternatif', 'icon' => 'clipboard', 'href' => route('admin.penilaian.index'), 'active' => request()->routeIs('admin.penilaian.*')],
                ['label' => 'Proses ELECTRE', 'icon' => 'calculator', 'href' => route('admin.electre.index'), 'active' => request()->routeIs('admin.electre.*')],
                [
                    'type' => 'group',
                    'label' => 'Hasil',
                    'icon' => 'chart',
                    'active' => request()->routeIs('admin.hasil-rekomendasi.*'),
                    'children' => [
                        ['label' => 'Hasil Rekomendasi', 'icon' => 'chart', 'href' => route('admin.hasil-rekomendasi.index'), 'active' => request()->routeIs('admin.hasil-rekomendasi.*')],
                        ['label' => 'Laporan', 'icon' => 'printer', 'href' => route('admin.hasil-rekomendasi.index'), 'active' => false],
                    ],
                ],
                [
                    'type' => 'group',
                    'label' => 'Master Data',
                    'icon' => 'database',
                    'active' => request()->routeIs('admin.dusuns.*') || request()->routeIs('admin.kriterias.*'),
                    'children' => [
                        ['label' => 'Data Dusun', 'icon' => 'map', 'href' => route('admin.dusuns.index'), 'active' => request()->routeIs('admin.dusuns.*')],
                        ['label' => 'Data Kriteria', 'icon' => 'list', 'href' => route('admin.kriterias.index'), 'active' => request()->routeIs('admin.kriterias.*')],
                    ],
                ],
                ['label' => 'Manajemen User', 'icon' => 'users', 'href' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')],
            ],
            'kepala_dusun' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('kepala-dusun.dashboard'), 'active' => request()->routeIs('kepala-dusun.dashboard')],
                ['label' => 'Ajukan Usulan', 'icon' => 'document', 'href' => route('kepala-dusun.usulan.create'), 'active' => request()->routeIs('kepala-dusun.usulan.create')],
                ['label' => 'Riwayat Usulan', 'icon' => 'history', 'href' => route('kepala-dusun.usulan.index'), 'active' => request()->routeIs('kepala-dusun.usulan.*') && ! request()->routeIs('kepala-dusun.usulan.create')],
            ],
            'kepala_desa' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('kepala-desa.dashboard'), 'active' => request()->routeIs('kepala-desa.dashboard')],
                ['label' => 'Hasil Rekomendasi', 'icon' => 'chart', 'href' => route('kepala-desa.hasil-rekomendasi.index'), 'active' => request()->routeIs('kepala-desa.hasil-rekomendasi.*')],
                ['label' => 'Laporan Keputusan', 'icon' => 'printer', 'href' => route('kepala-desa.keputusan-akhir.index'), 'active' => request()->routeIs('kepala-desa.keputusan-akhir.*')],
            ],
            default => [],
        };
    @endphp

    <div class="mobile-overlay" data-sidebar-close></div>

    <div class="app-shell">
        <aside class="sidebar" id="appSidebar" aria-label="Sidebar navigasi">
            <div class="brand">
                <div class="brand-mark">SP</div>
                <div class="brand-text">
                    <div class="brand-title">SPK Desa</div>
                    <div class="brand-subtitle">Metode ELECTRE</div>
                </div>
                <button class="icon-button sidebar-toggle" type="button" data-sidebar-toggle aria-label="Tutup atau buka sidebar" title="Tutup atau buka sidebar">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>
                <button class="icon-button sidebar-close" type="button" data-sidebar-close aria-label="Tutup menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                @foreach ($menus as $menu)
                    @if (($menu['type'] ?? 'item') === 'group')
                        <details class="sidebar-group {{ $menu['active'] ? 'active' : '' }}" @if ($menu['active']) open @endif>
                            <summary class="sidebar-group-summary">
                                <span class="sidebar-icon">
                                    @switch($menu['icon'])
                                        @case('chart')
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg>
                                            @break
                                        @default
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6c0-1.1 3.6-2 8-2s8 .9 8 2-3.6 2-8 2-8-.9-8-2Z" /><path d="M4 6v6c0 1.1 3.6 2 8 2s8-.9 8-2V6" /><path d="M4 12v6c0 1.1 3.6 2 8 2s8-.9 8-2v-6" /></svg>
                                    @endswitch
                                </span>
                                <span class="sidebar-group-label">{{ $menu['label'] }}</span>
                                <span class="sidebar-chevron">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                                </span>
                            </summary>
                            <div class="sidebar-submenu">
                                @foreach ($menu['children'] as $child)
                                    <a href="{{ $child['href'] }}" class="sidebar-link sidebar-sublink {{ $child['active'] ? 'active' : '' }}">
                                        <span class="sidebar-icon">
                                            @switch($child['icon'])
                                                @case('chart')
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5" /><path d="M4 19h16" /><path d="M8 16v-5M12 16V8M16 16v-7" /></svg>
                                                    @break
                                                @case('printer')
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5" /><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><path d="M7 14h10v7H7Z" /></svg>
                                                    @break
                                                @case('map')
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z" /><path d="M9 3v15M15 6v15" /></svg>
                                                    @break
                                                @case('list')
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" /><path d="M3 6h.01M3 12h.01M3 18h.01" /></svg>
                                                    @break
                                                @default
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1Z" /></svg>
                                            @endswitch
                                        </span>
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <a
                            href="{{ $menu['href'] }}"
                            class="sidebar-link {{ $menu['active'] ? 'active' : '' }}"
                        >
                            <span class="sidebar-icon">
                                @switch($menu['icon'])
                                    @case('users')
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /><path d="M22 21v-2a4 4 0 0 0-3-3.9" /><path d="M16 3.1a4 4 0 0 1 0 7.8" /></svg>
                                        @break
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
                    @endif
                @endforeach
            </nav>

            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ $roleLabel }}</div>
                    @if ($user->role === 'kepala_dusun')
                        <div class="user-role">{{ $user->dusun?->nama_dusun ?? 'Dusun belum terhubung' }}</div>
                    @endif
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

                    <div class="topbar-title topbar-logo-title">
                        <div class="topbar-logo-pair">
                            <img src="{{ asset('images/logo-kiri.png') }}" alt="Logo kiri">
                            <img src="{{ asset('images/logo-kanan.png') }}" alt="Logo kanan">
                        </div>
                        <div class="topbar-village-text">
                            <strong>Desa Barambang</strong>
                            <span>Kec. Sinjai Borong, Kab. Sinjai</span>
                            <small>Sulawesi Selatan</small>
                        </div>
                    </div>

                    <div class="topbar-actions">
                        <details class="profile-menu">
                            <summary class="profile-trigger">
                                <span class="avatar profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </summary>
                            <div class="profile-dropdown">
                                <a href="{{ route('profile.edit') }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><path d="M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                                    Profil Saya
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" data-loading-text="Logout...">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5" /><path d="M15 12H3" /><path d="M21 4v16" /></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </details>
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
            var toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');

            if (window.localStorage && localStorage.getItem('spk-sidebar-collapsed') === 'true') {
                body.classList.add('sidebar-collapsed');
            }

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

            toggleButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    body.classList.toggle('sidebar-collapsed');

                    if (window.localStorage) {
                        localStorage.setItem('spk-sidebar-collapsed', body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
                    }
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
                            setLoadingState(form);
                            form.submit();
                        }
                    });
                });
            });

            function setLoadingState(form) {
                form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    if (button.dataset.loading === 'true') {
                        return;
                    }

                    button.dataset.loading = 'true';
                    button.dataset.originalText = button.textContent.trim();
                    button.disabled = true;
                    button.textContent = button.dataset.loadingText || 'Memproses...';
                });
            }

            document.querySelectorAll('form:not(.js-confirm)').forEach(function (form) {
                form.addEventListener('submit', function () {
                    setLoadingState(form);
                });
            });
        });
    </script>
</body>
</html>
