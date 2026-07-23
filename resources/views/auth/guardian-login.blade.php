@extends('layouts.auth')

@section('title', 'Login Wali Murid')

@section('content')
    @php($brandImage = '/images/logo-gresik.png?v='.(file_exists(public_path('images/logo-gresik.png')) ? filemtime(public_path('images/logo-gresik.png')) : '1'))

    <main class="auth-shell">
        <section class="auth-showcase" aria-hidden="true">
            <div class="auth-showcase-board"></div>
            <div class="auth-showcase-copy">
                <span class="auth-kicker">Portal Wali Murid</span>
                <h1 class="auth-title">Pantau perkembangan anak di kelas catur dari satu halaman yang sederhana.</h1>
                <p class="auth-copy">
                    Masuk menggunakan kode siswa untuk melihat jadwal materi, perkembangan belajar,
                    dan ringkasan rapot anak saja.
                </p>

                <div class="auth-feature-list">
                    <div class="auth-feature-item">
                        <strong>Jadwal materi</strong>
                        <span>Lihat materi yang sudah diikuti anak pada tiap pertemuan.</span>
                    </div>
                    <div class="auth-feature-item">
                        <strong>Perkembangan belajar</strong>
                        <span>Pantau rata-rata nilai dan indeks perkembangan per bulan.</span>
                    </div>
                    <div class="auth-feature-item">
                        <strong>Rapot ringkas</strong>
                        <span>Semua nilai anak ditampilkan dalam satu dashboard wali murid.</span>
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
                    <h3 class="auth-form-title">Masuk Sebagai Wali Murid</h3>
                    <p class="auth-form-copy">Gunakan kode siswa yang telah diberikan oleh admin kelas catur.</p>
                </div>
            </div>

            <form class="auth-form" method="POST" action="{{ route('guardian.login.attempt', [], false) }}">
                @csrf

                <div class="form-group">
                    <label for="kode_siswa">Kode Siswa <span class="required-mark">*</span></label>
                    <input
                        id="kode_siswa"
                        name="kode_siswa"
                        type="text"
                        value="{{ old('kode_siswa') }}"
                        autocomplete="username"
                        placeholder="Contoh: 2607001"
                        data-uppercase
                    >
                    @error('kode_siswa')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <button class="btn btn-primary auth-submit" type="submit">Masuk sebagai Wali Murid</button>

                <div class="auth-alt-actions">
                    <p class="auth-helper-copy">Kembali ke login utama.</p>
                    <a class="btn btn-secondary" href="{{ route('login', [], false) }}">Masuk dengan Email Admin</a>
                </div>
            </form>
        </section>
    </main>
@endsection
