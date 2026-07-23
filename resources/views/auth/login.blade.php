@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    @php($brandImage = '/images/logo-gresik.png?v='.(file_exists(public_path('images/logo-gresik.png')) ? filemtime(public_path('images/logo-gresik.png')) : '1'))

    <main class="auth-shell">
        <section class="auth-showcase" aria-hidden="true">
            <div class="auth-showcase-board"></div>
            <div class="auth-showcase-copy">
                <span class="auth-kicker">Sistem Kelas Catur</span>
                <h1 class="auth-title">Kelas Catur yang rapi, aman, dan siap dipakai setiap hari.</h1>
                <p class="auth-copy">
                    Kelola pendaftaran, jadwal, perkembangan, dan rapot siswa dalam satu dashboard yang
                    profesional dan nyaman dipakai di desktop maupun handphone.
                </p>

                <div class="auth-feature-list">
                    <div class="auth-feature-item">
                        <strong>Pendaftaran siswa</strong>
                        <span>Data lebih tertata dengan form yang jelas dan cepat.</span>
                    </div>
                    <div class="auth-feature-item">
                        <strong>Riwayat & nilai</strong>
                        <span>Pantau pertemuan kelas dan perkembangan siswa tanpa ribet.</span>
                    </div>
                    <div class="auth-feature-item">
                        <strong>Rapot siap cetak</strong>
                        <span>Laporan siswa langsung siap dibuka kapan saja.</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-panel-header">
                <div class="auth-panel-topline">
                    <div class="brand auth-brand">
                        <div class="brand-mark">
                            <img src="{{ $brandImage }}" alt="Lambang Kabupaten Gresik">
                        </div>
                        <div class="brand-copy-block">
                            <h2 class="brand-title">KELAS CATUR</h2>
                            <p class="brand-subtitle">Kecamatan Bungah</p>
                        </div>
                    </div>

                    <button class="theme-toggle auth-panel-theme-toggle" type="button" data-theme-toggle aria-label="Ubah mode tampilan">
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
                </div>

                <div>
                    <h3 class="auth-form-title">Masuk dengan Email</h3>
                    <p class="auth-form-copy">Gunakan email admin dan password untuk mengakses seluruh data kelas catur.</p>
                </div>
            </div>

            <form
                class="auth-form"
                method="{{ config('auth.login_bypass.enabled') ? 'GET' : 'POST' }}"
                action="{{ config('auth.login_bypass.enabled') ? route('dashboard', [], false) : route('login.attempt', [], false) }}"
            >
                @unless (config('auth.login_bypass.enabled'))
                    @csrf
                @endunless

                <div class="form-group">
                    <label for="email">Email <span class="required-mark">*</span></label>
                    <input
                        id="email"
                        name="{{ config('auth.login_bypass.enabled') ? '' : 'email' }}"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="adminkc@gmail.com"
                    >
                    @error('email')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required-mark">*</span></label>
                    <div class="password-field">
                        <input
                            id="password"
                            name="{{ config('auth.login_bypass.enabled') ? '' : 'password' }}"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            data-password-input
                        >
                        <button
                            class="password-toggle"
                            type="button"
                            aria-label="Lihat kata sandi"
                            aria-pressed="false"
                            data-password-toggle
                        >
                            <span class="password-toggle-icon" aria-hidden="true"></span>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <label class="auth-remember">
                    <input type="checkbox" name="{{ config('auth.login_bypass.enabled') ? '' : 'remember' }}" value="1">
                    <span>Ingat saya di perangkat ini</span>
                </label>

                <button class="btn btn-primary auth-submit" type="submit">Masuk</button>

                <div class="auth-alt-actions">
                    <p class="auth-helper-copy">Jika Anda orang tua atau wali, gunakan portal khusus untuk melihat data anak.</p>
                    <a class="btn btn-secondary" href="{{ route('guardian.login', [], false) }}">Masuk sebagai Wali Murid</a>
                </div>
            </form>
        </section>
    </main>
@endsection

@section('scripts')
    <script>
        (function () {
            const passwordInput = document.querySelector('[data-password-input]');
            const passwordToggle = document.querySelector('[data-password-toggle]');

            if (!passwordInput || !passwordToggle) {
                return;
            }

            passwordToggle.addEventListener('click', function () {
                const shouldShow = passwordInput.type === 'password';
                passwordInput.type = shouldShow ? 'text' : 'password';
                passwordToggle.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
                passwordToggle.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
                passwordInput.focus();
            });
        }());
    </script>
@endsection
