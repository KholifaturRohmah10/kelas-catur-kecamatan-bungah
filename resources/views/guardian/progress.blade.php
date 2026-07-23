@extends('layouts.app')

@section('title', 'Perkembangan Siswa')
@section('page_heading', 'Perkembangan Siswa')
@section('page_description', 'Pantau perkembangan nilai anak.')

@section('content')
    @include('guardian._hero', ['student' => $student])

    @if ($latestScores->isEmpty())
        <section class="panel">
            <div class="empty-state">
                Belum ada perkembangan yang bisa ditampilkan karena nilai masih kosong.
            </div>
        </section>
    @else
        <section class="metric-grid progress-metric-grid">
            <article class="metric-card">
                <div class="metric-label">Penilaian masuk</div>
                <p class="metric-value">{{ $summary['total_sessions'] }}</p>
            </article>

            <article class="metric-card">
                <div class="metric-label">Nilai di atas 60</div>
                <p class="metric-value">{{ $summary['passed_sessions'] }}</p>
            </article>

            <article class="metric-card">
                <div class="metric-label">Indeks terbaru</div>
                <p class="metric-value">{{ $overview['latest_month_index'] !== null ? number_format($overview['latest_month_index'], 1, ',', '.') : '-' }}</p>
            </article>
        </section>

        <section class="two-column dashboard-sections">
            <article class="panel dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Nilai Terbaru</h3>
                        <p class="panel-copy">3 penilaian terakhir anak.</p>
                    </div>
                </div>

                <div class="list-stack dashboard-leader-list">
                    @foreach ($latestScores as $score)
                        <div class="mini-card dashboard-compact-card dashboard-insight-card">
                            <div class="dashboard-insight-head">
                                <div class="dashboard-insight-copy">
                                    <h4>{{ $score->classSession->judul }}</h4>
                                    <p class="dashboard-insight-meta">{{ $score->classSession->tanggal?->translatedFormat('d M Y') ?? '-' }}</p>
                                </div>

                                <div class="dashboard-insight-stat">
                                    <span class="dashboard-insight-stat-label">Nilai</span>
                                    <strong class="dashboard-insight-stat-value">{{ $score->nilai }}</strong>
                                </div>
                            </div>

                            <p class="dashboard-insight-body">{{ \App\Support\StudentPerformance::predicate((float) $score->nilai) }}</p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel dashboard-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Indeks Bulanan</h3>
                        <p class="panel-copy">Rata-rata nilai setiap bulan pembelajaran.</p>
                    </div>
                </div>

                @if ($monthlyIndices->isEmpty())
                    <div class="empty-state">
                        Indeks bulanan belum tersedia.
                    </div>
                @else
                    <div style="position: relative; height: 300px; width: 100%; margin-bottom: 2rem; padding: 0 1rem;">
                        <canvas id="progressChart"></canvas>
                    </div>

                    <div class="table-shell table-shell-scroll">
                        <table class="table table-mobile">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th>Rata-rata</th>
                                    <th>Pertemuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyIndices as $monthlyIndex)
                                    <tr>
                                        <td data-label="Bulan">{{ $monthlyIndex['month_label'] }}</td>
                                        <td data-label="Rata-rata">{{ number_format($monthlyIndex['average'], 1, ',', '.') }}</td>
                                        <td data-label="Pertemuan">{{ $monthlyIndex['session_count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>
    @endif
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('progressChart');
            if (ctx) {
                const rawData = @json($monthlyIndices->reverse()->values());
                const labels = rawData.map(item => item.month_label);
                const dataPoints = rawData.map(item => item.average);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Rata-rata Nilai',
                            data: dataPoints,
                            borderColor: '#0284c7', // Sky-600
                            backgroundColor: 'rgba(2, 132, 199, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#0284c7',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    stepSize: 20
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Nilai: ' + context.parsed.y;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
