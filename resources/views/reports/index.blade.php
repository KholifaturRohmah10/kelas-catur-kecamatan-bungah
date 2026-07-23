@extends('layouts.app')

@section('title', 'Cetak Rapot')
@section('page_heading', 'Cetak Rapot')
@section('page_description', '')

@section('content')
    @if (session('success'))
        <div style="background-color: #ecfdf5; color: #065f46; padding: 12px; border-radius: 4px; margin-bottom: 16px; border: 1px solid #34d399;">
            {{ session('success') }}
        </div>
    @endif

    <section class="toolbar">

        <form action="{{ route('reports.index') }}" method="GET" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 24px; width: 100%;">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                
                <div style="display: flex; gap: 24px; align-items: stretch; flex-wrap: wrap;">
                    <!-- Filter Semester -->
                    <div style="flex: 1; min-width: 250px;">
                        <label style="display: block; font-size: 0.9rem; font-weight: bold; color: #334155; margin-bottom: 8px;">Opsi 1: Pilih Semester (Otomatis)</label>
                        <select name="semester" onchange="this.form.submit()" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100%; background-color: #f8fafc; font-size: 0.95rem;">
                            <option value="">-- Pilih Semester --</option>
                            @foreach ($availableYears as $year)
                                <option value="ganjil-{{ $year }}" {{ request('semester') === "ganjil-{$year}" ? 'selected' : '' }}>Ganjil {{ $year }}</option>
                                <option value="genap-{{ $year }}" {{ request('semester') === "genap-{$year}" ? 'selected' : '' }}>Genap {{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Divider ATAU -->
                    <div style="display: flex; align-items: center; justify-content: center;">
                        <span style="color: #94a3b8; font-size: 0.85rem; font-weight: bold; padding: 8px;">ATAU</span>
                    </div>

                    <!-- Filter Kustom -->
                    <div style="flex: 1; min-width: 250px;">
                        <label style="display: block; font-size: 0.9rem; font-weight: bold; color: #334155; margin-bottom: 8px;">Opsi 2: Atur Rentang Bulan Kustom</label>
                        <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                            <input type="month" name="start_month" value="{{ $startMonth }}" style="flex: 1; padding: 7px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.95rem;">
                            <span style="color: #64748b; font-weight: bold;">-</span>
                            <input type="month" name="end_month" value="{{ $endMonth }}" style="flex: 1; padding: 7px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.95rem;">
                        </div>
                    </div>
                </div>

                <div class="table-actions" style="margin-top: 12px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px; flex-wrap: wrap;">
                    <button class="btn btn-primary" type="submit">Tampilkan</button>
                    <a class="btn btn-secondary" href="{{ route('reports.print-all', ['start_month' => $startMonth, 'end_month' => $endMonth, 'show_all' => $showAll ? 1 : 0]) }}" target="_blank">Cetak Seluruh Nilai</a>
                    <a class="btn btn-secondary" href="{{ route('reports.index', ['show_all' => 1]) }}">Tampilkan Seluruh Nilai Siswa</a>
                </div>
            </div>
        </form>
    </section>

    @php
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    @endphp

    <details style="margin-bottom: 24px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
        <summary style="cursor: pointer; font-weight: bold; color: #334155;">⚙️ Pengaturan Bulan Semester</summary>
        <div style="margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 12px;">Silakan atur bulan dimulainya dan berakhirnya setiap semester.</p>
            <form action="{{ route('reports.save-semester-config') }}" method="POST">
                @csrf
                <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 280px; padding: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <h4 style="margin-bottom: 12px; font-size: 0.95rem; color: #334155;">Semester Ganjil</h4>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <label style="font-size: 0.85rem; color: #64748b;">Mulai</label>
                            <select name="ganjil_start" style="padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 100px;">
                                @foreach($monthNames as $num => $name)
                                    <option value="{{ $num }}" {{ $semesterConfig['ganjil']['start'] == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <label style="font-size: 0.85rem; color: #64748b; margin-left: 8px;">Akhir</label>
                            <select name="ganjil_end" style="padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 100px;">
                                @foreach($monthNames as $num => $name)
                                    <option value="{{ $num }}" {{ $semesterConfig['ganjil']['end'] == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 280px; padding: 16px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <h4 style="margin-bottom: 12px; font-size: 0.95rem; color: #334155;">Semester Genap</h4>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <label style="font-size: 0.85rem; color: #64748b;">Mulai</label>
                            <select name="genap_start" style="padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 100px;">
                                @foreach($monthNames as $num => $name)
                                    <option value="{{ $num }}" {{ $semesterConfig['genap']['start'] == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <label style="font-size: 0.85rem; color: #64748b; margin-left: 8px;">Akhir</label>
                            <select name="genap_end" style="padding: 4px; border: 1px solid #cbd5e1; border-radius: 4px; min-width: 100px;">
                                @foreach($monthNames as $num => $name)
                                    <option value="{{ $num }}" {{ $semesterConfig['genap']['end'] == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 16px; padding: 6px 12px; font-size: 0.9rem;">Simpan Pengaturan</button>
            </form>
        </div>
    </details>

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
                            <th>Indeks {{ $selectedRangeLabel }}</th>
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
                                            <p class="student-name">{{ $row['student']->nama }}</p>
                                            @if ($row['student']->asal_sekolah)
                                                <p class="student-meta">{{ $row['student']->asal_sekolah }}</p>
                                            @endif
                                        </span>
                                        <span class="report-row-toggle-hint" aria-hidden="true">
                                            <span class="report-row-toggle-open">Show more</span>
                                            <span class="report-row-toggle-close">Show less</span>
                                        </span>
                                    </button>
                                </td>
                                <td class="report-row-detail-cell" data-label="Indeks {{ $selectedRangeLabel }}">
                                    @if ($row['range_index'])
                                        <span class="badge-accent badge">{{ number_format($row['range_index']['average'], 1, ',', '.') }}</span>
                                        <p class="student-meta">{{ $row['range_index']['predicate'] }}</p>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="report-row-detail-cell" data-label="Ringkasan bulanan">
                                    @if ($row['range_monthly_indices']->isEmpty())
                                        <span class="muted-text">Belum ada nilai.</span>
                                    @else
                                        <div class="tag-stack" style="max-height: 120px; overflow-y: auto; padding-right: 4px;">
                                            @foreach ($row['range_monthly_indices'] as $monthly)
                                                <span class="badge">
                                                    {{ $monthly['month_label'] }}: {{ number_format($monthly['average'], 1, ',', '.') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="report-row-detail-cell" data-label="Aksi">
                                    <div class="table-actions">
                                        <a class="btn btn-primary" href="{{ route('reports.show', ['student' => $row['student']->id, 'semester' => request('semester'), 'start_month' => $startMonth, 'end_month' => $endMonth, 'show_all' => $showAll ? 1 : 0]) }}" target="_blank">Buka Rapot</a>
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
