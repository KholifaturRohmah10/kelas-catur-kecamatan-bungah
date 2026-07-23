<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    @php($themeCss = '/css/kelas-catur.css?v='.(file_exists(public_path('css/kelas-catur.css')) ? filemtime(public_path('css/kelas-catur.css')) : '1'))
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

    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RKVNSHF7MM"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RKVNSHF7MM');
</script>

    <link rel="stylesheet" href="{{ $themeCss }}">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    @yield('head')
</head>
<body class="{{ isset($guardianStudent) ? 'guardian-portal' : '' }}">
    <script>
        (function() {
            try {
                if (localStorage.getItem('kelas-catur-sidebar-collapsed') === 'true') {
                    document.body.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    <button class="sidebar-backdrop" type="button" aria-hidden="true" tabindex="-1" data-sidebar-backdrop></button>

    <div class="shell">
        <aside class="sidebar" id="app-sidebar">
            <button class="sidebar-close" type="button" aria-label="Tutup menu" data-sidebar-close>
                <span class="sidebar-close-icon" aria-hidden="true"></span>
            </button>

            <div class="brand">
                <div class="brand-mark">
                    <img src="{{ '/images/logo-gresik.png?v='.(file_exists(public_path('images/logo-gresik.png')) ? filemtime(public_path('images/logo-gresik.png')) : '1') }}" alt="Lambang Kabupaten Gresik">
                </div>
                <div class="brand-copy-block">
                    <h1 class="brand-title">KELAS CATUR</h1>
                    <p class="brand-subtitle">Kecamatan Bungah</p>
                </div>
            </div>
            <nav class="nav">
                @if (isset($guardianStudent))
                    <a class="nav-link {{ request()->routeIs('guardian.dashboard') ? 'active' : '' }}" href="{{ route('guardian.dashboard') }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 10.5 12 4l8 6.5"/><path d="M6.5 10v9h11v-9"/><path d="M10 19v-5h4v5"/></svg>
                        </span>
                        <span>Dashboard</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('guardian.materials') ? 'active' : '' }}" href="{{ route('guardian.materials') }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M7 3v3"/><path d="M17 3v3"/><path d="M4.5 8h15"/><path d="M6 5h12A1.5 1.5 0 0 1 19.5 6.5V19A1.5 1.5 0 0 1 18 20.5H6A1.5 1.5 0 0 1 4.5 19V6.5A1.5 1.5 0 0 1 6 5Z"/><path d="M8 12h3"/><path d="M13 12h3"/><path d="M8 16h3"/></svg>
                        </span>
                        <span>Jadwal Materi</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('guardian.progress') ? 'active' : '' }}" href="{{ route('guardian.progress') }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M7 16v-5"/><path d="M12 16V7"/><path d="M17 16v-8"/><path d="m6 9 5-4 4 3 3-4"/></svg>
                        </span>
                        <span>Perkembangan Siswa</span>
                    </a>
                    <a class="nav-link {{ request()->routeIs('guardian.report') ? 'active' : '' }}" href="{{ route('guardian.report') }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M7 3.5h7l3 3V20A1.5 1.5 0 0 1 15.5 21.5h-9A1.5 1.5 0 0 1 5 20V5A1.5 1.5 0 0 1 6.5 3.5Z"/><path d="M14 3.5V7h3.5"/><path d="M8 12h6"/><path d="M8 16h5"/></svg>
                        </span>
                        <span>Rapot</span>
                    </a>
                @else
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
                @endif
            </nav>

        </aside>

        <main class="main-content">
            <header class="topbar">
                @php($pageDescription = trim($__env->yieldContent('page_description', '')))

                <div class="topbar-shell">
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
                            <h2 class="topbar-title">@yield('page_heading', 'Dashboard')</h2>
                            @if ($pageDescription !== '')
                                <p class="topbar-subtitle">{{ $pageDescription }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="topbar-metas">
                        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ubah mode tampilan">
                            <span class="theme-toggle-icon" aria-hidden="true">
                                <svg class="theme-toggle-glyph theme-toggle-glyph-moon" viewBox="0 0 24 24">
                                    <path d="M20.2 14.4A8.6 8.6 0 1 1 9.6 3.8a7.2 7.2 0 0 0 10.6 10.6Z"/>
                                </svg>
                                <svg class="theme-toggle-glyph theme-toggle-glyph-sun" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M12 2.5v2.2"/>
                                    <path d="M12 19.3v2.2"/>
                                    <path d="M21.5 12h-2.2"/>
                                    <path d="M4.7 12H2.5"/>
                                    <path d="m18.7 5.3-1.6 1.6"/>
                                    <path d="m6.9 17.1-1.6 1.6"/>
                                    <path d="m18.7 18.7-1.6-1.6"/>
                                    <path d="m6.9 6.9-1.6-1.6"/>
                                </svg>
                            </span>
                            <span class="sr-only" data-theme-label>Mode</span>
                        </button>

                        @if (auth()->user() || isset($guardianStudent))
                            <div class="topbar-account" data-account-menu>
                                @php($accountName = auth()->user()?->nama ?? ($guardianStudent->nama_wali ?? $guardianStudent->nama ?? ''))
                                @php($accountSecondary = auth()->user()?->email ?? (isset($guardianStudent) ? 'Wali dari '.$guardianStudent->nama : ''))
                                @php($accountRoleLabel = auth()->user()?->role_label ?? '')
                                @php($topbarUserInitial = $accountName !== '' ? (string) \Illuminate\Support\Str::of($accountName)->trim()->substr(0, 1)->upper() : '')
                                <button
                                    class="topbar-user"
                                    type="button"
                                    aria-label="Buka menu akun {{ $accountName }}"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    data-account-trigger
                                >
                                    <span class="topbar-user-avatar">{{ $topbarUserInitial }}</span>
                                    <span class="topbar-user-caret" aria-hidden="true">
                                        <svg viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
                                    </span>
                                </button>

                                <div class="topbar-account-panel" aria-hidden="true" data-account-panel>
                                    <div class="topbar-account-card">
                                        <span class="topbar-account-label">Masuk sebagai</span>
                                        <strong class="topbar-account-name">{{ $accountName }}</strong>
                                        @if ($accountRoleLabel !== '')
                                            <p class="topbar-account-role">{{ $accountRoleLabel }}</p>
                                        @endif
                                        @if ($accountSecondary !== '')
                                            <p class="topbar-account-email">{{ $accountSecondary }}</p>
                                        @endif
                                    </div>

                                    <form action="{{ isset($guardianStudent) ? route('guardian.logout', [], false) : route('logout', [], false) }}" method="POST" class="topbar-account-form">
                                        @csrf
                                        <button class="topbar-account-logout" type="submit">
                                            <span class="btn-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M10 5H6.5A1.5 1.5 0 0 0 5 6.5v11A1.5 1.5 0 0 0 6.5 19H10"/><path d="M15 8l4 4-4 4"/><path d="M19 12H10"/></svg>
                                            </span>
                                            <span>Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <div class="page-body">
                @include('partials.flash')

                @yield('content')
            </div>

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
            const clickableSelector = 'button, a.btn';
            const pressedClass = 'is-clicked';

            const markAsClicked = function (element) {
                if (!element || element.disabled || element.getAttribute('aria-disabled') === 'true') {
                    return;
                }

                if (element.__clickFeedbackTimer) {
                    window.clearTimeout(element.__clickFeedbackTimer);
                }

                element.classList.remove(pressedClass);

                window.requestAnimationFrame(function () {
                    element.classList.add(pressedClass);
                    element.__clickFeedbackTimer = window.setTimeout(function () {
                        element.classList.remove(pressedClass);
                    }, 180);
                });
            };

            document.addEventListener('pointerdown', function (event) {
                const target = event.target.closest(clickableSelector);

                if (!target) {
                    return;
                }

                markAsClicked(target);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                const target = event.target.closest(clickableSelector);

                if (!target) {
                    return;
                }

                markAsClicked(target);
            });
        }());

        (function () {
            const body = document.body;
            const sidebar = document.getElementById('app-sidebar');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const backdrop = document.querySelector('[data-sidebar-backdrop]');
            const closeButtons = document.querySelectorAll('[data-sidebar-close]');
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
                try {
                    localStorage.setItem('kelas-catur-sidebar-collapsed', 'true');
                } catch (e) {}
            };

            const openDesktopSidebar = function () {
                body.classList.remove('sidebar-collapsed');
                syncToggleState(true);
                try {
                    localStorage.setItem('kelas-catur-sidebar-collapsed', 'false');
                } catch (e) {}
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

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeMobileSidebar);
            });

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
            const accountMenus = document.querySelectorAll('[data-account-menu]');

            if (!accountMenus.length) {
                return;
            }

            const closeMenu = function (menu, options) {
                const settings = options || {};
                const trigger = menu.querySelector('[data-account-trigger]');
                const panel = menu.querySelector('[data-account-panel]');

                if (!trigger || !panel) {
                    return;
                }

                trigger.setAttribute('aria-expanded', 'false');
                panel.setAttribute('aria-hidden', 'true');
                menu.classList.remove('is-open');

                if (settings.restoreFocus && menu.contains(document.activeElement)) {
                    trigger.focus();
                }
            };

            const openMenu = function (menu) {
                const trigger = menu.querySelector('[data-account-trigger]');
                const panel = menu.querySelector('[data-account-panel]');

                if (!trigger || !panel) {
                    return;
                }

                trigger.setAttribute('aria-expanded', 'true');
                panel.setAttribute('aria-hidden', 'false');
                menu.classList.add('is-open');
            };

            const closeAllMenus = function (exceptMenu, options) {
                accountMenus.forEach(function (menu) {
                    if (menu === exceptMenu) {
                        return;
                    }

                    closeMenu(menu, options);
                });
            };

            accountMenus.forEach(function (menu) {
                const trigger = menu.querySelector('[data-account-trigger]');
                const panel = menu.querySelector('[data-account-panel]');

                if (!trigger || !panel) {
                    return;
                }

                trigger.addEventListener('click', function () {
                    const isExpanded = trigger.getAttribute('aria-expanded') === 'true';

                    closeAllMenus(menu);

                    if (isExpanded) {
                        closeMenu(menu);
                        return;
                    }

                    openMenu(menu);
                });

                menu.addEventListener('focusout', function () {
                    window.setTimeout(function () {
                        if (menu.contains(document.activeElement)) {
                            return;
                        }

                        closeMenu(menu);
                    }, 0);
                });
            });

            document.addEventListener('pointerdown', function (event) {
                accountMenus.forEach(function (menu) {
                    if (menu.contains(event.target)) {
                        return;
                    }

                    closeMenu(menu);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                closeAllMenus(null, { restoreFocus: true });
            });

            window.addEventListener('resize', function () {
                closeAllMenus(null);
            });
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

        (function () {
            const syncUppercaseValue = function (field) {
                if (!field || typeof field.value !== 'string') {
                    return;
                }

                const start = typeof field.selectionStart === 'number' ? field.selectionStart : null;
                const end = typeof field.selectionEnd === 'number' ? field.selectionEnd : null;
                const uppercaseValue = field.value.toLocaleUpperCase('id-ID');

                if (field.value === uppercaseValue) {
                    return;
                }

                field.value = uppercaseValue;

                if (start !== null && end !== null && document.activeElement === field) {
                    field.setSelectionRange(start, end);
                }
            };

            document.querySelectorAll('[data-uppercase]').forEach(function (field) {
                field.addEventListener('input', function () {
                    syncUppercaseValue(field);
                });

                syncUppercaseValue(field);
            });
        }());
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('select[data-searchable]').forEach(function(el) {
                new TomSelect(el, {
                    plugins: ['dropdown_input'],
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
