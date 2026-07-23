@extends('layouts.app')

@section('title', 'Rapot')
@section('page_heading', 'Rapot')
@section('page_description', 'Rapot online semua pertemuan anak.')

@section('content')
    @include('guardian._hero', ['student' => $student])

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Rapot Online</h3>
                <p class="panel-copy">Lihat semua pertemuan, rekap per bulan, dan nilai lengkap milik {{ $student->nama }} dalam satu halaman.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a class="btn btn-secondary" href="{{ route('guardian.report') }}">Semua Pertemuan</a>
                <a class="btn btn-primary" href="{{ route('guardian.report.print', ['month' => $reportFilters['month'], 'semester' => $reportFilters['semester']]) }}" target="_blank">Cetak Rapot</a>
            </div>
        </div>

        @if ($availableMonths->isEmpty())
            <div class="empty-state">
                Rapot belum bisa ditampilkan karena nilai masih kosong.
            </div>
        @else
            <section class="two-column">
                <form method="GET" action="{{ route('guardian.report') }}" class="report-block">
                    <div class="report-section-title">Filter Per Bulan</div>
                    <p class="report-section-copy">Pilih satu bulan untuk melihat semua pertemuan pada bulan tersebut.</p>

                    <div class="form-group spacer-top">
                        <label for="month">Bulan</label>
                        <select id="month" name="month" data-searchable>
                            <option value="">Pilih bulan</option>
                            @foreach ($availableMonths as $monthOption)
                                <option value="{{ $monthOption['value'] }}" @selected($reportFilters['month'] === $monthOption['value'])>
                                    {{ $monthOption['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-actions spacer-top">
                        <button class="btn btn-primary" type="submit">Tampilkan Bulan</button>
                        @if ($reportFilters['month'] !== null)
                            <a class="btn btn-secondary" href="{{ route('guardian.report') }}">Reset</a>
                        @endif
                    </div>
                </form>

                <form method="GET" action="{{ route('guardian.report') }}" class="report-block">
                    <div class="report-section-title">Filter Semester</div>
                    <p class="report-section-copy">Pilih semester untuk melihat rapot sesuai periode yang diinginkan.</p>

                    <div class="form-group spacer-top">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester" data-searchable>
                            <option value="">Pilih semester</option>
                            @foreach ($availableYears as $year)
                                <option value="ganjil-{{ $year }}" @selected($reportFilters['semester'] === "ganjil-{$year}")>
                                    Semester Ganjil {{ $year }}
                                </option>
                                <option value="genap-{{ $year }}" @selected($reportFilters['semester'] === "genap-{$year}")>
                                    Semester Genap {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="table-actions spacer-top">
                        <button class="btn btn-primary" type="submit">Tampilkan Semester</button>
                        @if ($reportFilters['semester'] !== null)
                            <a class="btn btn-secondary" href="{{ route('guardian.report') }}">Reset</a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="report-block spacer-top">
                <div class="report-section-title">Periode Rapot</div>
                <p class="report-section-copy">Menampilkan periode {{ $reportPeriodLabel }} dengan seluruh nilai yang masuk pada periode tersebut.</p>

                <div class="tag-stack spacer-top">
                    <span class="chip badge-accent">{{ $reportSummary['total_sessions'] }} pertemuan</span>
                    <span class="chip">{{ $reportMonthlyBreakdown->count() }} bulan tercakup</span>
                    @if ($reportAvailabilityLabel)
                        <span class="chip">{{ $reportAvailabilityLabel }}</span>
                    @endif
                </div>
            </section>

            <section class="report-scoreboard spacer-top">
                <article class="report-score-card">
                    <div class="report-score-label">Pertemuan Ditampilkan</div>
                    <p class="report-score-value">{{ $reportSummary['total_sessions'] }}</p>
                </article>

                <article class="report-score-card">
                    <div class="report-score-label">Rata-rata Nilai</div>
                    <p class="report-score-value">{{ $reportSummary['average_score'] !== null ? number_format($reportSummary['average_score'], 1, ',', '.') : '-' }}</p>
                </article>

                <article class="report-score-card">
                    <div class="report-score-label">Nilai Tertinggi</div>
                    <p class="report-score-value">{{ $reportSummary['highest_score'] ?? '-' }}</p>
                </article>

                <article class="report-score-card">
                    <div class="report-score-label">Nilai Terendah</div>
                    <p class="report-score-value">{{ $reportSummary['lowest_score'] ?? '-' }}</p>
                </article>
            </section>

            @if ($reportScores->isEmpty())
                <div class="empty-state spacer-top">
                    Belum ada pertemuan pada periode yang dipilih.
                </div>
            @else
                <section class="report-block spacer-top">
                    <div class="report-section-title">Daftar Semua Pertemuan</div>
                    <p class="report-section-copy">Setiap pertemuan pada periode rapot ditampilkan lengkap beserta materi dan nilainya.</p>

                    <div class="table-shell spacer-top guardian-report-table-shell">
                        <table class="table table-mobile">
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
                                @foreach ($reportScores as $score)
                                    <tr>
                                        <td data-label="No">{{ $loop->iteration }}</td>
                                        <td data-label="Tanggal">{{ $score->classSession->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                                        <td data-label="Materi">
                                            <p class="student-name">{{ $score->classSession->judul }}</p>
                                            <p class="student-meta">{{ \Illuminate\Support\Str::limit($score->classSession->materialPreviewText(), 84) }}</p>
                                            @if ($score->classSession->hasMaterialFile())
                                                <div class="material-reference-actions material-reference-actions-compact">
                                                    @if ($score->classSession->materialTypeLabel())
                                                        <span class="chip">{{ $score->classSession->materialTypeLabel() }}</span>
                                                    @endif
                                                    <a class="material-link" href="{{ route('sessions.material-file', $score->classSession) }}" target="_blank" rel="noopener">Buka File</a>
                                                </div>
                                            @endif
                                        </td>
                                        <td data-label="Nilai">{{ $score->nilai }}</td>
                                        <td data-label="Predikat">{{ \App\Support\StudentPerformance::predicate((float) $score->nilai) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="guardian-report-card-grid spacer-top">
                        @foreach ($reportScores as $score)
                            @php($predicate = \App\Support\StudentPerformance::predicate((float) $score->nilai))
                            <article class="mini-card guardian-report-card">
                                <div class="guardian-report-card-topline">
                                    <div class="guardian-report-card-field guardian-report-card-field-index">
                                        <span class="guardian-report-card-label">No</span>
                                        <strong class="guardian-report-card-index">{{ $loop->iteration }}</strong>
                                    </div>

                                    <div class="guardian-report-card-field guardian-report-card-field-date">
                                        <span class="guardian-report-card-label">Tanggal</span>
                                        <span class="guardian-report-card-value">{{ $score->classSession->tanggal?->translatedFormat('d M Y') ?? '-' }}</span>
                                    </div>

                                    <div class="guardian-report-card-scorebox">
                                        <span class="guardian-report-card-label">Nilai</span>
                                        <strong class="guardian-report-card-score">{{ $score->nilai }}</strong>
                                    </div>
                                </div>

                                <div class="guardian-report-card-body">
                                    <span class="guardian-report-card-label">Materi</span>
                                    <h4 class="guardian-report-card-title">{{ $score->classSession->judul }}</h4>
                                    <p class="guardian-report-card-material">
                                        {{ \Illuminate\Support\Str::limit($score->classSession->materialPreviewText(), 40) }}
                                    </p>
                                    @if ($score->classSession->hasMaterialFile())
                                        <div class="material-reference-actions material-reference-actions-compact">
                                            @if ($score->classSession->materialTypeLabel())
                                                <span class="chip">{{ $score->classSession->materialTypeLabel() }}</span>
                                            @endif
                                            <a class="material-link material-link-light" href="{{ route('sessions.material-file', $score->classSession) }}" target="_blank" rel="noopener">Buka File</a>
                                        </div>
                                    @endif
                                </div>

                                <div class="guardian-report-card-footer">
                                    <span class="guardian-report-card-label">Predikat</span>
                                    <strong class="guardian-report-card-predicate">{{ $predicate }}</strong>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="report-block spacer-top">
                <div class="report-section-title">Catatan Pembimbing</div>
                <p class="report-section-copy">Catatan ini mengikuti rata-rata nilai pada periode rapot yang sedang ditampilkan.</p>
                <p class="report-note spacer-top">{{ $teacherNote }}</p>
            </section>
        @endif
    </section>
@endsection
