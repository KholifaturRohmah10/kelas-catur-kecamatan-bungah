@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_heading', 'Dashboard')
@section('page_description', '')

@section('content')
    <section class="hero-panel dashboard-hero">
        <div>
            <h3 class="hero-title">Ringkasan kelas hari ini</h3>
            <p class="hero-copy">Pantau siswa, pertemuan, nilai, dan rapot dari satu tempat.</p>
        </div>

        <div class="hero-quote">
            <strong>Prioritas</strong>
            <p class="hero-copy">Lengkapi jadwal dan nilai terbaru.</p>
        </div>
    </section>

    <section class="metric-grid dashboard-metric-grid">
        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Total siswa</div>
            <p class="metric-value">{{ $stats['total_students'] }}</p>
        </article>

        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Pertemuan kelas</div>
            <p class="metric-value">{{ $stats['total_sessions'] }}</p>
        </article>

        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Rata-rata nilai</div>
            <p class="metric-value">{{ number_format($stats['average_score'], 1, ',', '.') }}</p>
        </article>

        <article class="metric-card dashboard-metric-card">
            <div class="metric-label">Tingkat lulus</div>
            <p class="metric-value">{{ number_format($stats['pass_rate'], 1, ',', '.') }}%</p>
        </article>
    </section>

    <section class="two-column dashboard-sections">
        <article class="panel dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Pertemuan Terbaru</h3>
                </div>
                <a class="btn btn-secondary dashboard-panel-action" href="{{ route('sessions.index') }}">Kelola Jadwal</a>
            </div>

            @if ($latestSessions->isEmpty())
                <div class="empty-state">
                    Belum ada jadwal kelas.
                </div>
            @else
                <div class="list-stack dashboard-session-list">
                    @foreach ($latestSessions as $session)
                        <div class="mini-card dashboard-compact-card">
                            <div class="panel-header">
                                <div>
                                    <h4>{{ $session->title }}</h4>
                                    <p>{{ $session->session_date->translatedFormat('d F Y') }}</p>
                                </div>
                                <span class="badge">{{ $session->participant_count }} siswa</span>
                            </div>
                            <p class="dashboard-session-material">{{ \Illuminate\Support\Str::limit($session->material, 140) }}</p>
                            <div class="tag-stack spacer-top dashboard-session-tags">
                                <span class="chip badge-accent">Rata-rata {{ number_format((float) $session->average_score, 1, ',', '.') }}</span>
                                <span class="chip dashboard-session-extra">{{ $session->passed_students_count }} siswa di atas 60</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel dashboard-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Indeks Bulanan Teratas</h3>
                    <p class="panel-copy">Menampilkan 3 siswa terbaik bulan ini.</p>
                </div>
                <a class="btn btn-secondary dashboard-panel-action" href="{{ route('reports.index') }}">Lihat Rapot</a>
            </div>

            @if ($monthlyLeaders->isEmpty())
                <div class="empty-state">
                    Belum ada nilai pada bulan ini.
                </div>
            @else
                <div class="list-stack dashboard-leader-list">
                    @foreach ($monthlyLeaders as $row)
                        <div class="mini-card dashboard-compact-card">
                            <div class="panel-header">
                                <div>
                                    <h4>{{ $row['student']->name }}</h4>
                                </div>
                                <span class="badge">{{ number_format($row['current_month']['average'], 1, ',', '.') }}</span>
                            </div>
                            <p>{{ $row['predicate'] }}</p>
                            <p class="student-meta">{{ $row['current_month']['session_count'] }} pertemuan bulan ini</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

@endsection
