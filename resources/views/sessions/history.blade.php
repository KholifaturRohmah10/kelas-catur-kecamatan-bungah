@extends('layouts.app')

@section('title', 'Riwayat Kelas')
@section('page_heading', 'Riwayat Kelas')
@section('page_description', '')

@section('content')
    @php
        $isFilteringByMonth = filled($selectedMonth);
    @endphp

    <section class="toolbar history-toolbar">
        <div>
            <strong>{{ $isFilteringByMonth ? 'Riwayat bulan '.$selectedMonthLabel : 'Seluruh riwayat kelas' }}</strong>
            <p class="toolbar-copy">Jumlah data: {{ $sessionCount }} pertemuan</p>
        </div>

        <form action="{{ route('session-history.index') }}" method="GET">
            <div class="table-actions history-filter-actions">
                <input type="month" name="month" value="{{ $selectedMonth ?? '' }}">
                <a class="btn btn-secondary" href="{{ route('session-history.index') }}">Semua Data</a>
                <button class="btn btn-primary" type="submit">Tampilkan</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Riwayat Pertemuan</h3>
                <p class="panel-copy">Tampilan ringkas. Klik <strong>Show more</strong> untuk membuka detail materi, catatan, dan nilai siswa.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('sessions.index') }}">Tambah Pertemuan</a>
        </div>

        @if ($sessions->isEmpty())
            <div class="empty-state">
                @if ($isFilteringByMonth)
                    Belum ada riwayat kelas pada {{ $selectedMonthLabel }}.
                @else
                    Belum ada riwayat kelas yang tercatat.
                @endif
            </div>
        @else
            <div class="history-session-list">
                @foreach ($sessions as $classSession)
                    <article class="history-session-card" data-collapsed="true">
                        <div class="history-session-summary">
                            <div class="history-session-copy">
                                <p class="history-session-date">{{ $classSession->tanggal->translatedFormat('d F Y') }}</p>
                                <h4 class="history-session-title">{{ $classSession->judul }}</h4>
                                <p class="student-meta">
                                    {{ $classSession->assessed_count }} siswa dinilai
                                    @if ($classSession->absent_count > 0)
                                        | {{ $classSession->absent_count }} tidak masuk
                                    @endif
                                    | Rata-rata {{ $classSession->average_score !== null ? number_format((float) $classSession->average_score, 1, ',', '.') : '-' }}
                                </p>
                            </div>
                            <span class="badge">{{ $classSession->scores_count }} tercatat</span>
                        </div>

                        <div class="history-session-detail">
                                <div class="history-detail-grid">
                                    <div class="history-detail-block">
                                        <span class="history-detail-label">{{ $classSession->hasMaterialFile() ? 'File Materi' : 'Materi' }}</span>
                                        @if ($classSession->hasMaterialFile())
                                            <div class="material-reference">
                                                <p>{{ $classSession->materialDisplayName() }}</p>
                                                <div class="material-reference-actions">
                                                    @if ($classSession->materialTypeLabel())
                                                        <span class="chip">{{ $classSession->materialTypeLabel() }}</span>
                                                    @endif
                                                    <a class="material-link" href="{{ route('sessions.material-file', $classSession) }}" target="_blank" rel="noopener">Buka File</a>
                                                </div>
                                            </div>
                                        @else
                                            <p>{{ $classSession->material }}</p>
                                        @endif
                                    </div>

                                @if ($classSession->catatan)
                                    <div class="history-detail-block">
                                        <span class="history-detail-label">Catatan</span>
                                        <p>{{ $classSession->catatan }}</p>
                                    </div>
                                @endif

                                <div class="history-detail-block">
                                    <span class="history-detail-label">Daftar Nilai Siswa</span>
                                    <div class="history-score-list">
                                        @foreach ($classSession->scores as $score)
                                            <div class="history-score-item">
                                                <span class="history-score-name">{{ $score->student->nama }}</span>
                                                <span class="history-score-value">{{ $score->hasScore() ? $score->nilai : 'Tidak masuk' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="history-session-actions history-session-actions-detail">
                                <a class="btn btn-secondary" href="{{ route('session-history.edit', $classSession) }}">Edit</a>
                                <form class="inline-form" action="{{ route('session-history.destroy', $classSession) }}" method="POST" onsubmit="return confirm('Hapus riwayat kelas dan seluruh nilai pada pertemuan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>

                        <div class="history-session-actions history-session-actions-toggle">
                            <button class="btn btn-light history-session-toggle" type="button" aria-expanded="false" data-history-toggle>
                                <span class="history-session-toggle-open">Show more</span>
                                <span class="history-session-toggle-close">Show less</span>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-history-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const card = button.closest('.history-session-card');

                if (!card) {
                    return;
                }

                const isExpanded = card.getAttribute('data-collapsed') === 'false';

                card.setAttribute('data-collapsed', isExpanded ? 'true' : 'false');
                button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
            });
        });
    </script>
@endsection
