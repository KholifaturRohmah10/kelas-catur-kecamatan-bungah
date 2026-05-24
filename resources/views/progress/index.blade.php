@extends('layouts.app')

@section('title', 'Perkembangan Siswa')
@section('page_heading', 'Perkembangan Siswa')
@section('page_description', '')

@section('content')
    <section class="metric-grid progress-metric-grid">
        <article class="metric-card">
            <div class="metric-label">Total materi</div>
            <p class="metric-value">{{ $overview['materials_count'] }}</p>
        </article>

        <article class="metric-card">
            <div class="metric-label">Total penilaian</div>
            <p class="metric-value">{{ $overview['total_assessments'] }}</p>
        </article>

        <article class="metric-card">
            <div class="metric-label">Nilai di atas 60</div>
            <p class="metric-value">{{ $overview['passed_assessments'] }}</p>
        </article>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Grafik Siswa di Atas Nilai 60</h3>
                <p class="panel-copy">Menampilkan materi dalam 1 bulan terakhir.</p>
            </div>
        </div>

        @if ($chartSessions->isEmpty())
            <div class="empty-state">
                Belum ada data jadwal kelas dan nilai siswa dalam 1 bulan terakhir.
            </div>
        @else
            @if ($chartSessions->count() > 8)
                <p class="chart-helper">Geser ke samping untuk melihat seluruh materi.</p>
            @endif

            <div class="chart-scroll">
                <div class="chart-grid" style="--chart-columns: {{ max($chartSessions->count(), 1) }};">
                    @foreach ($chartSessions as $session)
                        @php
                            $barHeight = $peakPassed > 0 ? ($session->passed_students_count / $peakPassed) * 100 : 0;
                        @endphp
                        <div class="bar-card">
                            <div class="bar-track">
                                <div class="bar-fill" style="height: {{ $barHeight }}%;"></div>
                            </div>
                            <div class="bar-value">{{ $session->passed_students_count }}</div>
                            <p class="bar-meta">dari {{ $session->participant_count }} siswa</p>
                            <p class="bar-label">{{ \Illuminate\Support\Str::limit($session->title, 22) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Nilai Siswa</h3>
                <p class="panel-copy">Ringkasan jumlah pertemuan yang diikuti dan IPS setiap siswa.</p>
            </div>
        </div>

        @if ($studentProgress->isEmpty())
            <div class="empty-state">
                Data nilai siswa belum tersedia.
            </div>
        @else
            <div class="table-shell">
                <table class="table table-mobile">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Pertemuan diikuti</th>
                            <th>IPS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studentProgress as $student)
                            <tr>
                                <td data-label="Siswa">
                                    <p class="student-name">{{ $student->name }}</p>
                                </td>
                                <td data-label="Pertemuan diikuti">{{ $student->session_count }}</td>
                                <td data-label="IPS">{{ $student->average_score !== null ? number_format((float) $student->average_score, 1, ',', '.') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
