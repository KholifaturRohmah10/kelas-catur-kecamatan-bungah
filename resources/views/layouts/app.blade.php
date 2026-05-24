<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    @php($themeCss = asset('css/kelas-catur.css').'?v='.filemtime(public_path('css/kelas-catur.css')))
    @php($brandImage = asset('images/logo-gresik.png').'?v='.filemtime(public_path('images/logo-gresik.png')))
    <script>
        (function () {
            try {
                const storedTheme = localStorage.getItem('kelas-catur-theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.dataset.theme = storedTheme || (prefersDark ? 'dark' : 'light');
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        }());
    </script>
    <link rel="stylesheet" href="{{ $themeCss }}">
    @yield('head')
</head>
<body>
    <button class="sidebar-backdrop" type="button" aria-hidden="true" tabindex="-1" data-sidebar-backdrop></button>

    <div class="shell">
        <aside class="sidebar" id="app-sidebar">
            @php($currentUser = auth()->user())
            <div class="brand">
                <div class="brand-mark">
                    <img src="{{ $brandImage }}" alt="Lambang Kabupaten Gresik">
                </div>
                <div class="brand-copy-block">
                    <h1 class="brand-title">KELAS CATUR</h1>
                    <p class="brand-subtitle">Kecamatan Bungah</p>
                </div>
            </div>
            <nav class="nav">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 10.5 12 4l8 6.5"/><path d="M6.5 10v9h11v-9"/><path d="M10 19v-5h4v5"/></svg>
                    </span>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link {{ request()->routeIs('registrations.*') ? 'active' : '' }}" href="{{ route('registrations.index') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/><path d="M6 3.5h12A1.5 1.5 0 0 1 19.5 5v14A1.5 1.5 0 0 1 18 20.5H6A1.5 1.5 0 0 1 4.5 19V5A1.5 1.5 0 0 1 6 3.5Z"/></svg>
                    </span>
                    <span>Daftar</span>
                </a>
                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
                    </span>
                    <span>Data Siswa</span>
                </a>
                <a class="nav-link {{ request()->routeIs('sessions.*') ? 'active' : '' }}" href="{{ route('sessions.index') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M7 3v3"/><path d="M17 3v3"/><path d="M4.5 8h15"/><path d="M6 5h12A1.5 1.5 0 0 1 19.5 6.5V19A1.5 1.5 0 0 1 18 20.5H6A1.5 1.5 0 0 1 4.5 19V6.5A1.5 1.5 0 0 1 6 5Z"/><path d="M8 12h3"/><path d="M13 12h3"/><path d="M8 16h3"/></svg>
                    </span>
                    <span>Jadwal Kelas</span>
                </a>
                <a class="nav-link {{ request()->routeIs('session-history.*') ? 'active' : '' }}" href="{{ route('session-history.index') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 1 0 2.35-5.65"/><path d="M4 5v5h5"/><path d="M12 8v4l3 2"/></svg>
                    </span>
                    <span>Riwayat Kelas</span>
                </a>
                <a class="nav-link {{ request()->routeIs('progress.*') ? 'active' : '' }}" href="{{ route('progress.index') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M7 16v-5"/><path d="M12 16V7"/><path d="M17 16v-8"/><path d="m6 9 5-4 4 3 3-4"/></svg>
                    </span>
                    <span>Perkembangan Siswa</span>
                </a>
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M7 3.5h7l3 3V20A1.5 1.5 0 0 1 15.5 21.5h-9A1.5 1.5 0 0 1 5 20V5A1.5 1.5 0 0 1 6.5 3.5Z"/><path d="M14 3.5V7h3.5"/><path d="M8 12h6"/><path d="M8 16h5"/></svg>
                    </span>
                    <span>Cetak Rapot</span>
                </a>
            </nav>

            @if ($currentUser)
                <div class="sidebar-footer">
                    <div class="sidebar-user-card">
                        <span class="sidebar-user-label">Masuk sebagai</span>
                        <strong class="sidebar-user-name">{{ $currentUser->name }}</strong>
                        <p class="sidebar-user-email">{{ $currentUser->email }}</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-light sidebar-logout-button" type="submit">
                            <span class="btn-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M10 5H6.5A1.5 1.5 0 0 0 5 6.5v11A1.5 1.5 0 0 0 6.5 19H10"/><path d="M15 8l4 4-4 4"/><path d="M19 12H10"/></svg>
                            </span>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            @endif
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-heading">
                    <button
                        class="sidebar-toggle"
                        type="button"
                        aria-label="Buka menu"
                        aria-controls="app-sidebar"
                        aria-expanded="false"
                        data-sidebar-toggle
                    >
                        <span class="sidebar-toggle-dots" aria-hidden="true">
                            <span class="sidebar-toggle-dot"></span>
                            <span class="sidebar-toggle-dot"></span>
                            <span class="sidebar-toggle-dot"></span>
                        </span>
                    </button>

                    <div class="topbar-copy">
                        <p class="page-kicker">@yield('kicker', 'Kelas Catur')</p>
                        <h2 class="topbar-title">@yield('page_heading', 'Dashboard')</h2>
                        @php($pageDescription = trim($__env->yieldContent('page_description', '')))
                        @if ($pageDescription !== '')
                            <p class="topbar-subtitle">{{ $pageDescription }}</p>
                        @endif
                    </div>
                </div>

                <div class="topbar-metas">
                    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ubah mode tampilan">
                        <span class="theme-toggle-icon" aria-hidden="true"></span>
                        <span data-theme-label>Mode</span>
                    </button>

                    <div class="meta-card meta-card-date">
                        <span class="meta-card-label">Hari ini</span>
                        <strong class="meta-card-value">{{ now()->translatedFormat('d F Y') }}</strong>
                    </div>
                </div>
            </header>

            @include('partials.flash')

            @yield('content')
        </main>
    </div>

    <script>
        (function () {
            const root = document.documentElement;
            const themeToggles = document.querySelectorAll('[data-theme-toggle]');
            const themeLabels = document.querySelectorAll('[data-theme-label]');
            const storageKey = 'kelas-catur-theme';

            const applyTheme = function (theme) {
                const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
                root.dataset.theme = normalizedTheme;
                try {
                    localStorage.setItem(storageKey, normalizedTheme);
                } catch (error) {
                    // Mode tetap berubah walaupun browser membatasi penyimpanan lokal.
                }
                themeLabels.forEach(function (label) {
                    label.textContent = normalizedTheme === 'dark' ? 'Dark' : 'Light';
                });
                themeToggles.forEach(function (button) {
                    button.setAttribute('aria-pressed', normalizedTheme === 'dark' ? 'true' : 'false');
                    button.setAttribute('aria-label', normalizedTheme === 'dark' ? 'Ubah ke mode light' : 'Ubah ke mode dark');
                });
            };

            themeToggles.forEach(function (button) {
                button.addEventListener('click', function () {
                    applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
                });
            });

            applyTheme(root.dataset.theme || 'light');
        }());

        (function () {
            const body = document.body;
            const sidebar = document.getElementById('app-sidebar');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const backdrop = document.querySelector('[data-sidebar-backdrop]');
            const desktopBreakpoint = 1180;

            if (!body || !sidebar || !toggle || !backdrop) {
                return;
            }

            const isDesktop = function () {
                return window.innerWidth > desktopBreakpoint;
            };

            const syncToggleState = function (isExpanded) {
                toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                toggle.setAttribute('aria-label', isExpanded ? 'Tutup menu' : 'Buka menu');
            };

            const closeMobileSidebar = function () {
                body.classList.remove('sidebar-open');
                syncToggleState(false);
            };

            const openMobileSidebar = function () {
                body.classList.add('sidebar-open');
                syncToggleState(true);
            };

            const closeDesktopSidebar = function () {
                body.classList.add('sidebar-collapsed');
                syncToggleState(false);
            };

            const openDesktopSidebar = function () {
                body.classList.remove('sidebar-collapsed');
                syncToggleState(true);
            };

            const syncSidebarMode = function () {
                if (isDesktop()) {
                    body.classList.remove('sidebar-open');
                    syncToggleState(!body.classList.contains('sidebar-collapsed'));
                    return;
                }

                body.classList.remove('sidebar-collapsed');
                syncToggleState(body.classList.contains('sidebar-open'));
            };

            toggle.addEventListener('click', function () {
                if (isDesktop()) {
                    if (body.classList.contains('sidebar-collapsed')) {
                        openDesktopSidebar();
                        return;
                    }

                    closeDesktopSidebar();
                    return;
                }

                if (body.classList.contains('sidebar-open')) {
                    closeMobileSidebar();
                    return;
                }

                openMobileSidebar();
            });

            backdrop.addEventListener('click', closeMobileSidebar);

            sidebar.querySelectorAll('.nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (!isDesktop()) {
                        closeMobileSidebar();
                    }
                });
            });

            window.addEventListener('resize', function () {
                syncSidebarMode();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    if (isDesktop()) {
                        closeDesktopSidebar();
                        return;
                    }

                    closeMobileSidebar();
                }
            });

            syncSidebarMode();
        }());

        (function () {
            document.querySelectorAll('[data-required-alert-form]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.checkValidity()) {
                        return;
                    }

                    event.preventDefault();
                    alert('Mohon lengkapi semua kolom wajib bertanda * merah terlebih dahulu.');
                    form.reportValidity();
                });
            });
        }());
    </script>

    @yield('scripts')
</body>
</html>
