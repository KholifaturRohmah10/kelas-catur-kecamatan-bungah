@extends('layouts.app')

@section('title', 'Jadwal Materi')
@section('page_heading', 'Jadwal Materi')
@section('page_description', 'Semua materi yang sudah diikuti anak.')

@section('content')
    @include('guardian._hero', ['student' => $student])

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Daftar Materi</h3>
                <p class="panel-copy">Menampilkan semua materi dan nilai milik {{ $student->nama }}.</p>
            </div>
        </div>

        @if ($scores->isEmpty())
            <div class="empty-state">
                Belum ada jadwal materi yang tercatat untuk siswa ini.
            </div>
        @else
            <section class="two-column spacer-bottom">
                <form method="GET" action="{{ route('guardian.materials') }}" class="report-block">
                    <div class="report-section-title">Filter Per Bulan</div>
                    <p class="report-section-copy">Pilih satu bulan untuk melihat semua materi pada bulan tersebut.</p>

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
                            <a class="btn btn-secondary" href="{{ route('guardian.materials') }}">Reset</a>
                        @endif
                    </div>
                </form>

                <form method="GET" action="{{ route('guardian.materials') }}" class="report-block">
                    <div class="report-section-title">Filter Semester</div>
                    <p class="report-section-copy">Pilih semester untuk melihat materi sesuai periode yang diinginkan.</p>

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
                            <a class="btn btn-secondary" href="{{ route('guardian.materials') }}">Reset</a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="report-block spacer-bottom">
                <div class="report-section-title">Periode Materi</div>
                <p class="report-section-copy">Menampilkan periode {{ $reportPeriodLabel }} dengan seluruh materi yang masuk pada periode tersebut.</p>
                
                <div class="tag-stack spacer-top">
                    <span class="chip badge-accent">{{ $filteredScores->count() }} materi</span>
                    @if ($reportAvailabilityLabel)
                        <span class="chip">{{ $reportAvailabilityLabel }}</span>
                    @endif
                </div>
            </section>

            @if ($filteredScores->isEmpty())
                <div class="empty-state">
                    Belum ada materi pada periode yang dipilih.
                </div>
            @else
                <div class="list-stack dashboard-session-list guardian-material-list">
                    @foreach ($filteredScores as $score)
                        <details class="mini-card dashboard-compact-card guardian-material-card">
                        <summary class="guardian-material-summary">
                            <div class="guardian-material-copy">
                                <h4>{{ $score->classSession->judul }}</h4>
                                <p>{{ $score->classSession->tanggal?->translatedFormat('d F Y') ?? '-' }}</p>
                            </div>

                            <div class="guardian-material-summary-row">
                                <div class="guardian-material-scorebox {{ $score->isAbsent() ? 'guardian-material-scorebox-absent' : '' }}">
                                    <span class="guardian-material-score-label">{{ $score->hasScore() ? 'Nilai' : 'Status' }}</span>
                                    <strong class="guardian-material-score-value">{{ $score->hasScore() ? $score->nilai : 'Absen' }}</strong>
                                </div>

                                <span class="guardian-material-toggle" aria-hidden="true">
                                    <span class="guardian-material-toggle-label guardian-material-toggle-open">Detail Materi</span>
                                    <span class="guardian-material-toggle-label guardian-material-toggle-close">Tutup Detail</span>
                                    <span class="guardian-material-toggle-icon"></span>
                                </span>
                            </div>
                        </summary>

                        <div class="guardian-material-details">
                            <div class="guardian-material-detail-block">
                                <span class="guardian-material-detail-label">{{ $score->classSession->hasMaterialFile() ? 'File Materi' : 'Materi Lengkap' }}</span>
                                @if ($score->classSession->hasMaterialFile())
                                    <div class="material-reference material-reference-inline">
                                        <p class="guardian-material-detail-text">{{ $score->classSession->materialDisplayName() }}</p>
                                        <div class="material-reference-actions">
                                            @if ($score->classSession->materialTypeLabel())
                                                <span class="chip">{{ $score->classSession->materialTypeLabel() }}</span>
                                            @endif
                                            <a class="material-link" href="{{ route('sessions.material-file', $score->classSession) }}" target="_blank" rel="noopener">Buka File</a>
                                        </div>
                                    </div>
                                @else
                                    <p class="guardian-material-detail-text">{{ $score->classSession->material }}</p>
                                @endif
                            </div>

                            @if ($score->classSession->catatan)
                                <div class="guardian-material-detail-block">
                                    <span class="guardian-material-detail-label">Catatan Kelas</span>
                                    <p class="guardian-material-detail-text">{{ $score->classSession->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
        @endif
    </section>
@endsection
