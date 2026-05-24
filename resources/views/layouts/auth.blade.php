<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    @php($themeCss = asset('css/kelas-catur.css').'?v='.filemtime(public_path('css/kelas-catur.css')))
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
        <span class="theme-toggle-icon" aria-hidden="true"></span>
        <span data-theme-label>Mode</span>
    </button>

    @include('partials.flash')

    @yield('content')

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
    </script>

    @yield('scripts')
</body>
</html>
