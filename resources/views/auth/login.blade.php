@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    @php($brandImage = asset('images/logo-gresik.png').'?v='.filemtime(public_path('images/logo-gresik.png')))

    <main class="auth-shell">
        <section class="auth-showcase" aria-hidden="true">
            <div class="auth-showcase-board"></div>
            <div class="auth-showcase-copy">
                <span class="auth-kicker">Sistem Administrasi</span>
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
                <div class="brand auth-brand">
                    <div class="brand-mark">
                        <img src="{{ $brandImage }}" alt="Lambang Kabupaten Gresik">
                    </div>
                    <div class="brand-copy-block">
                        <h2 class="brand-title">KELAS CATUR</h2>
                        <p class="brand-subtitle">Kecamatan Bungah</p>
                    </div>
                </div>

                <div>
                    <h3 class="auth-form-title">Masuk dengan Email</h3>
                    <p class="auth-form-copy">Gunakan email admin dan kata sandi untuk mengakses seluruh data kelas catur.</p>
                </div>
            </div>

            <form class="auth-form" method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email admin</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        placeholder="adminkc@gmail.com"
                        required
                    >
                    @error('email')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Kata sandi</label>
                    <div class="password-field">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            required
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
                    <input type="checkbox" name="remember" value="1">
                    <span>Ingat saya di perangkat ini</span>
                </label>

                <button class="btn btn-primary auth-submit" type="submit">Masuk</button>
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
