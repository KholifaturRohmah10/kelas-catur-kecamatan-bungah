@extends('layouts.app')

@section('title', 'Cetak Rapot')
@section('page_heading', 'Cetak Rapot')
@section('page_description', '')

@section('content')
    <section class="toolbar">
        <div>
            <strong>Indeks bulan {{ $selectedMonthLabel }}</strong>
        </div>

        <form action="{{ route('reports.index') }}" method="GET">
            <div class="table-actions">
                <input type="month" name="month" value="{{ $selectedMonth }}">
                <button class="btn btn-primary" type="submit">Tampilkan</button>
                <a class="btn btn-secondary" href="{{ route('reports.print-all', ['month' => $selectedMonth]) }}" target="_blank">Cetak Seluruh Nilai</a>
            </div>
        </form>
    </section>

    <section class="metric-grid">
        <article class="metric-card">
            <div class="metric-label">Siswa bernilai</div>
            <p class="metric-value">{{ $stats['students_with_scores'] }}</p>
        </article>

        <article class="metric-card">
            <div class="metric-label">Rata-rata bulan ini</div>
            <p class="metric-value">{{ $stats['selected_month_average'] !== null ? number_format($stats['selected_month_average'], 1, ',', '.') : '-' }}</p>
        </article>

        <article class="metric-card">
            <div class="metric-label">Indeks terbaik</div>
            <p class="metric-value">{{ $stats['best_index'] !== null ? number_format($stats['best_index'], 1, ',', '.') : '-' }}</p>
        </article>

        <article class="metric-card">
            <div class="metric-label">Siswa teratas</div>
            <p class="metric-value" style="font-size: 1.3rem;">{{ $stats['best_student'] ?: '-' }}</p>
        </article>
    </section>

    @if ($availableMonths->isNotEmpty())
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Pilihan Bulan Tersedia</h3>
                </div>
            </div>
            <div class="tag-stack">
                @foreach ($availableMonths as $month)
                    @php
                        $monthLabel = ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $month)->locale('id')->translatedFormat('F Y'));
                    @endphp
                    <a class="badge {{ $month === $selectedMonth ? 'badge-accent' : '' }}" href="{{ route('reports.index', ['month' => $month]) }}">{{ $monthLabel }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Rekap Siswa</h3>
            </div>
        </div>

        @if ($reports->isEmpty())
            <div class="empty-state">
                Belum ada data siswa untuk dibuatkan rapot.
            </div>
        @else
            <div class="table-shell">
                <table class="table table-mobile reports-table-mobile">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Indeks {{ $selectedMonthLabel }}</th>
                            <th>Rata-rata total</th>
                            <th>Ringkasan bulanan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $row)
                            <tr data-collapsed="true">
                                <td class="report-primary-cell" data-label="Siswa">
                                    <button class="report-row-toggle" type="button" aria-expanded="false" data-report-toggle>
                                        <span class="report-row-toggle-copy">
                                            <p class="student-name">{{ $row['student']->name }}</p>
                                            <p class="student-meta">{{ $row['student']->student_code }}{{ $row['student']->school_name ? ' - '.$row['student']->school_name : '' }}</p>
                                        </span>
                                        <span class="report-row-toggle-hint" aria-hidden="true">
                                            <span class="report-row-toggle-open">Show more</span>
                                            <span class="report-row-toggle-close">Show less</span>
                                        </span>
                                    </button>
                                </td>
                                <td class="report-row-detail-cell" data-label="Indeks {{ $selectedMonthLabel }}">
                                    @if ($row['selected_index'])
                                        <span class="badge-accent badge">{{ number_format($row['selected_index']['average'], 1, ',', '.') }}</span>
                                        <p class="student-meta">{{ $row['selected_index']['predicate'] }}</p>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="report-row-detail-cell" data-label="Rata-rata total">
                                    {{ $row['summary']['average_score'] !== null ? number_format($row['summary']['average_score'], 1, ',', '.') : '-' }}
                                </td>
                                <td class="report-row-detail-cell" data-label="Ringkasan bulanan">
                                    @if ($row['monthly_indices']->isEmpty())
                                        <span class="muted-text">Belum ada nilai.</span>
                                    @else
                                        <div class="tag-stack">
                                            @foreach ($row['monthly_indices'] as $monthly)
                                                <span class="badge">
                                                    {{ $monthly['month_label'] }}: {{ number_format($monthly['average'], 1, ',', '.') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="report-row-detail-cell" data-label="Aksi">
                                    <div class="table-actions">
                                        <a class="btn btn-primary" href="{{ route('reports.show', $row['student']) }}" target="_blank">Buka Rapot</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

@section('scripts')
    <script>
        (function () {
            const mobileQuery = window.matchMedia('(max-width: 720px)');

            document.querySelectorAll('[data-report-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!mobileQuery.matches) {
                        return;
                    }

                    const row = button.closest('tr');

                    if (!row) {
                        return;
                    }

                    const isExpanded = row.getAttribute('data-collapsed') === 'false';

                    row.setAttribute('data-collapsed', isExpanded ? 'true' : 'false');
                    button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                });
            });
        }());
    </script>
@endsection
