<!DOCTYPE html>
@php
    $themeCssHref = '/css/kelas-catur.css?v='.(file_exists(public_path('css/kelas-catur.css')) ? filemtime(public_path('css/kelas-catur.css')) : '1');
    $brandImageHref = '/images/logo-gresik.png?v='.(file_exists(public_path('images/logo-gresik.png')) ? filemtime(public_path('images/logo-gresik.png')) : '1');
@endphp
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seluruh Nilai Siswa | KELAS CATUR Kecamatan Bungah</title>
    <link rel="stylesheet" href="{{ $themeCssHref }}">
</head>
<body class="report-body">
    <section class="report-sheet report-sheet-compact report-sheet-wide">
        <header class="report-header">
            <div class="brand-mark">
                <img src="{{ $brandImageHref }}" alt="Lambang Kabupaten Gresik">
            </div>

            <div class="report-header-copy">
                <p class="page-kicker report-kicker">Rekap Seluruh Nilai Siswa</p>
                <h1 class="report-title">KELAS CATUR Kecamatan Bungah</h1>
                <p class="report-subtitle">Ringkasan dan seluruh nilai siswa untuk acuan cetak dan pengelolaan kelas.</p>
            </div>

            <div class="report-meta report-meta-box">
                <span class="identity-label">Indeks Pilihan</span>
                <strong>{{ $selectedRangeLabel }}</strong>
            </div>
        </header>

        <section class="report-block spacer-top">
            <div class="report-section-title">Daftar Siswa dan Nilai</div>
            <p class="report-section-copy">{{ $reports->count() }} siswa tercantum dalam rekap ini.</p>
        </section>

        <section class="report-student-stack">
            @foreach ($reports as $row)
                @php($statusBadgeClass = $row['student']->status === 'Aktif' ? 'status-tag-active' : 'status-tag-inactive')
                <article class="report-block report-student-block">
                    <div class="report-student-header">
                        <div>
                            <h2 class="report-student-title">{{ $row['student']->nama }}</h2>
                            @if ($row['student']->asal_sekolah)
                                <p class="report-meta">{{ $row['student']->asal_sekolah }}</p>
                            @endif
                        </div>

                        <div class="report-student-badges">
                            <span class="report-score-note {{ $statusBadgeClass }}">{{ $row['student']->status }}</span>
                            <span class="report-score-note">
                                Indeks {{ $selectedRangeLabel }}:
                                {{ $row['range_index'] ? number_format($row['range_index']['average'], 1, ',', '.') : '-' }}
                            </span>
                        </div>
                    </div>

                    @if ($row['range_scores']->isEmpty())
                        <div class="empty-state spacer-top">
                            Belum ada nilai untuk siswa ini pada rentang waktu terpilih.
                        </div>
                    @else
                        <div class="table-shell report-table-shell spacer-top">
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
                                    @foreach ($row['range_scores'] as $score)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $score->classSession->tanggal->translatedFormat('d M Y') }}</td>
                                            <td>
                                                <p class="report-material-title">{{ $score->classSession->judul }}</p>
                                                <p class="report-material-copy">{{ \Illuminate\Support\Str::limit($score->classSession->materialPreviewText(), 90) }}</p>
                                            </td>
                                            <td>{{ $score->nilai }}</td>
                                            <td>{{ \App\Support\StudentPerformance::predicate((float) $score->nilai) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    </section>

    <div class="report-toolbar report-toolbar-bottom report-toolbar-wide">
        <a class="btn btn-secondary" href="{{ route('reports.index', ['start_month' => $startMonth, 'end_month' => $endMonth, 'show_all' => $showAll ? 1 : 0]) }}">Kembali</a>
        <button class="btn btn-primary" type="button" onclick="window.print()">Cetak</button>
    </div>
</body>
</html>
