<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
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
    <link rel="stylesheet" href="{{ $themeCss }}">
    @yield('head')
</head>
<body class="auth-body">
    <button class="theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Ubah mode tampilan">
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

    @include('partials.flash')

    <div class="auth-stage">
        @yield('content')
    </div>

    <footer class="page-footer auth-page-footer">
        <span class="page-footer-text">Developed by @aemmaaa</span>
    </footer>

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
    </script>

    @yield('scripts')
</body>
</html>
