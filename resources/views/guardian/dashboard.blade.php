@extends('layouts.app')

@section('title', 'Dashboard Wali Murid')
@section('page_heading', 'Dashboard Wali Murid')
@section('page_description', 'Ringkasan belajar anak.')

@section('content')
    @include('guardian._hero', ['student' => $student])

    @php
        $performancePredicate = \App\Support\StudentPerformance::predicate($overview['average_score'] !== null ? (float) $overview['average_score'] : null);
        $masteryRate = $summary['total_sessions'] > 0
            ? (int) round(($summary['passed_sessions'] / $summary['total_sessions']) * 100)
            : null;
        $latestMaterial = $latestMaterials->first();
    @endphp

    <section class="metric-grid dashboard-metric-grid">
        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Materi diikuti</div>
            <p class="metric-value">{{ $overview['materials_count'] }}</p>
        </article>

        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Rata-rata nilai</div>
            <p class="metric-value">{{ $overview['average_score'] !== null ? number_format($overview['average_score'], 1, ',', '.') : '-' }}</p>
        </article>

        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Nilai tertinggi</div>
            <p class="metric-value">{{ $overview['highest_score'] ?? '-' }}</p>
        </article>

        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Indeks bulan ini</div>
            <p class="metric-value">{{ $overview['current_month_index'] !== null ? number_format($overview['current_month_index'], 1, ',', '.') : '-' }}</p>
        </article>
    </section>

    <section class="dashboard-sections guardian-dashboard-overview">
        <article class="panel dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Jadwal Materi</h3>
                    <p class="panel-copy">Lihat materi dan nilai dari 1 pertemuan terakhir anak.</p>
                </div>
                <a class="btn btn-secondary dashboard-panel-action" href="{{ route('guardian.materials') }}">Buka Halaman</a>
            </div>

            @if ($latestMaterials->isEmpty())
                <div class="empty-state">
                    Belum ada materi yang tercatat untuk siswa ini.
                </div>
            @else
                <div class="list-stack dashboard-session-list">
                    @foreach ($latestMaterials as $score)
                        <div class="mini-card dashboard-compact-card dashboard-insight-card {{ $score->isAbsent() ? 'dashboard-insight-card-absent' : '' }}">
                            <div class="dashboard-insight-head">
                                <div class="dashboard-insight-copy">
                                    <div class="dashboard-insight-title-row">
                                        <h4>{{ $score->classSession->judul }}</h4>
                                        @if ($score->isAbsent())
                                            <span class="dashboard-inline-status dashboard-inline-status-absent">Absen</span>
                                        @endif
                                    </div>
                                    <p class="dashboard-insight-meta">{{ $score->classSession->tanggal?->translatedFormat('d F Y') ?? '-' }}</p>
                                </div>

                                @if ($score->hasScore())
                                    <div class="dashboard-insight-stat">
                                        <span class="dashboard-insight-stat-label">Nilai</span>
                                        <strong class="dashboard-insight-stat-value">{{ $score->nilai }}</strong>
                                    </div>
                                @endif
                            </div>

                            @if ($score->classSession->hasMaterialFile())
                                <div class="material-reference">
                                    <p class="dashboard-insight-body dashboard-session-material">{{ \Illuminate\Support\Str::limit($score->classSession->materialPreviewText(), 120) }}</p>
                                    <div class="material-reference-actions">
                                        @if ($score->classSession->materialTypeLabel())
                                            <span class="chip">{{ $score->classSession->materialTypeLabel() }}</span>
                                        @endif
                                        <a class="material-link" href="{{ route('sessions.material-file', $score->classSession) }}" target="_blank" rel="noopener">Buka File</a>
                                    </div>
                                </div>
                            @else
                                <p class="dashboard-insight-body dashboard-session-material">{{ \Illuminate\Support\Str::limit($score->classSession->material, 120) }}</p>
                            @endif

                            @if ($score->isAbsent())
                                <p class="dashboard-insight-note">Siswa tidak masuk pada pertemuan ini.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel dashboard-panel guardian-summary-panel">
            <div class="guardian-summary-head">
                <span class="guardian-summary-kicker">Snapshot Belajar</span>
                <div class="guardian-summary-title-row">
                    <h3 class="guardian-summary-title">{{ $performancePredicate }}</h3>
                    <span class="guardian-summary-pill">
                        {{ $overview['average_score'] !== null ? 'Rata-rata '.number_format($overview['average_score'], 1, ',', '.') : 'Belum ada nilai' }}
                    </span>
                </div>
                <p class="guardian-summary-copy">{{ $teacherNote }}</p>
            </div>

            <div class="guardian-summary-grid">
                <article class="guardian-summary-stat">
                    <span class="guardian-summary-stat-label">Indeks terbaru</span>
                    <strong class="guardian-summary-stat-value">
                        {{ $overview['latest_month_index'] !== null ? number_format($overview['latest_month_index'], 1, ',', '.') : '-' }}
                    </strong>
                    <p class="guardian-summary-stat-note">{{ $overview['latest_month_label'] ?? 'Belum ada rekap bulanan' }}</p>
                </article>

                <article class="guardian-summary-stat">
                    <span class="guardian-summary-stat-label">Penilaian tuntas</span>
                    <strong class="guardian-summary-stat-value">{{ $summary['passed_sessions'] }}/{{ $summary['total_sessions'] }}</strong>
                    <p class="guardian-summary-stat-note">{{ $masteryRate !== null ? $masteryRate.'% nilai di atas 60' : 'Menunggu penilaian pertama' }}</p>
                </article>
            </div>

            <div class="guardian-summary-story">
                <span class="guardian-summary-story-label">Update terakhir</span>
                @if ($latestMaterial !== null)
                    <strong class="guardian-summary-story-title">{{ $latestMaterial->classSession->judul }}</strong>
                    <p class="guardian-summary-story-copy">
                        {{ $latestMaterial->classSession->tanggal?->translatedFormat('d F Y') ?? 'Tanggal pertemuan belum diisi' }}
                    </p>
                @else
                    <strong class="guardian-summary-story-title">Belum ada pertemuan terbaru</strong>
                    <p class="guardian-summary-story-copy">Ringkasan pertemuan terbaru akan muncul di sini setelah pembimbing mengisi materi atau nilai.</p>
                @endif
            </div>

            <div class="guardian-summary-actions">
                <a class="btn btn-secondary" href="{{ route('guardian.progress') }}">Lihat Perkembangan</a>
                <p class="guardian-summary-footnote">Buka halaman perkembangan untuk melihat tren nilai dan indeks bulanan anak lebih lengkap.</p>
            </div>
        </article>
    </section>

    <section class="panel dashboard-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Rapot</h3>
                <p class="panel-copy">Ringkasan nilai lengkap dan catatan pembimbing untuk anak.</p>
            </div>
            <a class="btn btn-secondary dashboard-panel-action" href="{{ route('guardian.report') }}">Buka Halaman</a>
        </div>

        <div class="report-scoreboard report-scoreboard-3">
            <article class="report-score-card">
                <div class="report-score-label">Jumlah Penilaian</div>
                <p class="report-score-value">{{ $summary['total_sessions'] }}</p>
            </article>

            <article class="report-score-card">
                <div class="report-score-label">Rata-rata Nilai</div>
                <p class="report-score-value">{{ $summary['average_score'] !== null ? number_format($summary['average_score'], 1, ',', '.') : '-' }}</p>
            </article>

            <article class="report-score-card">
                <div class="report-score-label">Indeks Terbaru</div>
                <p class="report-score-value">{{ $overview['latest_month_index'] !== null ? number_format($overview['latest_month_index'], 1, ',', '.') : '-' }}</p>
            </article>
        </div>

        <section class="report-block spacer-top">
            <div class="report-section-title">Catatan Pembimbing</div>
            <p class="report-note">{{ $teacherNote }}</p>
        </section>
    </section>
@endsection
