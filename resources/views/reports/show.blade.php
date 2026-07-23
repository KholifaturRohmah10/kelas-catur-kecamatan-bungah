<!DOCTYPE html>
@php
    $themeCssHref = '/css/kelas-catur.css?v='.(file_exists(public_path('css/kelas-catur.css')) ? filemtime(public_path('css/kelas-catur.css')) : '1');
    $brandImageHref = '/images/logo-gresik.png?v='.(file_exists(public_path('images/logo-gresik.png')) ? filemtime(public_path('images/logo-gresik.png')) : '1');
@endphp
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapot {{ $student->nama }} | KELAS CATUR Kecamatan Bungah</title>
    <link rel="stylesheet" href="{{ $themeCssHref }}">
</head>
<body class="report-body">
    <section class="report-sheet report-sheet-compact">
        <header class="report-header report-header-no-meta">
            <div class="brand-mark">
                <img src="{{ $brandImageHref }}" alt="Lambang Kabupaten Gresik">
            </div>

            <div class="report-header-copy">
                <p class="page-kicker report-kicker">Laporan Hasil Belajar Siswa</p>
                <h1 class="report-title">KELAS CATUR Kecamatan Bungah</h1>
                <p class="report-subtitle">Rapot perkembangan siswa program pembinaan catur</p>
            </div>
        </header>

        <section class="report-block spacer-top">
            <div class="report-section-title">Identitas Siswa</div>
            <table class="report-identity-table">
                <tbody>
                    <tr>
                        <th>Nama Siswa</th>
                        <td>{{ $student->nama }}</td>
                        <th>Gender</th>
                        <td>{{ $student->jenis_kelamin }}</td>
                    </tr>
                    <tr>
                        <th>Sekolah</th>
                        <td>{{ $student->asal_sekolah ?: '-' }}</td>
                        <th>Status</th>
                        <td>{{ $student->status }}</td>
                    </tr>
                    <tr>
                        <th>Orang Tua / Wali</th>
                        <td>{{ $student->nama_wali ?: '-' }}</td>
                        @if (!empty($semesterLabel))
                            <th>Semester</th>
                            <td>{{ str_replace('Semester ', '', $semesterLabel) }}</td>
                        @elseif (isset($selectedRangeLabel) && $selectedRangeLabel !== 'Seluruh Waktu')
                            <th>Periode</th>
                            <td>{{ $selectedRangeLabel }}</td>
                        @else
                            <th>Tanggal Daftar</th>
                            <td>{{ $student->tanggal_daftar?->translatedFormat('d F Y') ?: '-' }}</td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="report-scoreboard spacer-top">
            <article class="report-score-card">
                <div class="report-score-label">Jumlah Penilaian</div>
                <p class="report-score-value">{{ $summary['total_sessions'] }}</p>
            </article>

            <article class="report-score-card">
                <div class="report-score-label">Rata-rata Nilai</div>
                <p class="report-score-value">{{ $summary['average_score'] !== null ? number_format($summary['average_score'], 1, ',', '.') : '-' }}</p>
                @if ($summary['average_score'] !== null)
                    <span class="report-score-note">{{ \App\Support\StudentPerformance::predicate($summary['average_score']) }}</span>
                @endif
            </article>

            <article class="report-score-card">
                <div class="report-score-label">Nilai Tertinggi</div>
                <p class="report-score-value">{{ $summary['highest_score'] ?? '-' }}</p>
            </article>

            <article class="report-score-card">
                <div class="report-score-label">Indeks Terbaru</div>
                <p class="report-score-value">{{ $summary['latest_month_index'] !== null ? number_format($summary['latest_month_index'], 1, ',', '.') : '-' }}</p>
            </article>
        </section>

        <section class="report-block spacer-top">
            <div class="report-section-title">Nilai Materi</div>
            <p class="report-section-copy">Seluruh materi yang diikuti siswa</p>

            @if ($scores->isEmpty())
                <div class="empty-state">
                    Belum ada data penilaian.
                </div>
            @else
                <div class="table-shell report-table-shell">
                    <table class="table report-table-compact">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Materi</th>
                                <th>Nilai</th>
                                <th>Predikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scores as $score)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $score->classSession->tanggal->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <p class="report-material-title">{{ $score->classSession->judul }}</p>
                                        <p class="report-material-copy">{{ \Illuminate\Support\Str::limit($score->classSession->materialPreviewText(), 72) }}</p>
                                    </td>
                                    <td>{{ $score->nilai }}</td>
                                    <td>{{ \App\Support\StudentPerformance::predicate((float) $score->nilai) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="report-block spacer-top">
            <div class="report-section-title">Catatan Pembimbing</div>
            <p class="report-note">{{ $teacherNote }}</p>
        </section>

        <section class="signature-grid signature-grid-compact">
            <article class="signature-card signature-card-formal">
                <p class="report-meta">Mengetahui,</p>
                <strong>Orang Tua / Wali</strong>
                <div class="signature-space signature-space-compact"></div>
                <strong>({{ $student->nama_wali ?: '........................................' }})</strong>
            </article>

            <article class="signature-card signature-card-formal signature-card-mentor">
                <p class="report-meta signature-place-date">GRESIK, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
                <strong>Pembimbing</strong>
                <div class="signature-space signature-space-compact"></div>
                <strong>(........................................)</strong>
            </article>
        </section>
    </section>

    <div class="report-toolbar report-toolbar-bottom">
        @if (isset($isGuardian) && $isGuardian)
            <a class="btn btn-secondary" href="{{ route('guardian.report') }}">Kembali</a>
        @else
            <a class="btn btn-secondary" href="{{ route('reports.index') }}">Kembali</a>
        @endif
        <button class="btn btn-primary" type="button" onclick="window.print()">Cetak</button>
    </div>
</body>
</html>
