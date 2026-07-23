<section class="hero-panel dashboard-hero guardian-hero">
    @php($guardianAddress = $student->alamat ?: 'Alamat belum diisi')
    @php($guardianInfo = collect([
        $student->asal_sekolah ?: 'Sekolah belum diisi',
        $student->nama_wali ?: 'Orang tua belum diisi',
        \Illuminate\Support\Str::limit($guardianAddress, 34),
    ])->implode(' | '))
    <div class="guardian-hero-main">
        <h3 class="hero-title guardian-hero-title">{{ $student->nama }}</h3>
        <p class="hero-copy guardian-hero-copy" title="{{ $guardianAddress }}">{{ $guardianInfo }}</p>
    </div>
</section>
