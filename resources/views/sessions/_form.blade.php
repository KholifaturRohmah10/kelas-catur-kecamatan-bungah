@php
    $sessionForm = $classSession ?? null;
    $selectedStudents = collect(old('student_ids', $selectedStudents ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $scoreValues = old('scores', $scoreValues ?? []);
    $activeStudentCount = $students->where('status', 'Aktif')->count();
    $hasInactiveStudents = $students->contains(fn ($student) => $student->status !== 'Aktif');
    $hasLockedInactiveScores = $sessionForm?->scores?->contains(fn ($score) => $score->student && $score->student->status !== 'Aktif') ?? false;
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="title">Judul pertemuan</label>
        <input id="title" name="title" type="text" value="{{ old('title', $sessionForm?->title ?? '') }}" placeholder="Contoh: Taktik Pembukaan dan Kontrol Tengah" data-uppercase required>
        @error('title')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="session_date">Tanggal</label>
        <input id="session_date" name="session_date" type="date" value="{{ old('session_date', $sessionForm?->session_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        @error('session_date')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="material">Materi</label>
        <textarea id="material" name="material" placeholder="Tulis ringkasan materi, target latihan, atau catatan pembelajaran untuk pertemuan ini" required>{{ old('material', $sessionForm?->material ?? '') }}</textarea>
        @error('material')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="notes">Catatan tambahan</label>
        <textarea id="notes" name="notes" placeholder="Opsional: catatan instruktur, fokus evaluasi, atau kebutuhan tindak lanjut">{{ old('notes', $sessionForm?->notes ?? '') }}</textarea>
        @error('notes')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="spacer-top">
    <div class="panel-header">
        <div>
            <h4 class="panel-title">Pilih Siswa dan Isi Nilai</h4>
            @if ($hasInactiveStudents)
                <p class="score-table-note muted-text">
                    Siswa nonaktif tidak bisa dipilih untuk penilaian baru.
                    @if ($hasLockedInactiveScores)
                        Nilai lama yang sudah tersimpan tetap dikunci.
                    @endif
                </p>
            @endif
        </div>
        <span class="badge">{{ $activeStudentCount }} siswa aktif tersedia</span>
    </div>

    <div class="table-shell table-shell-scroll score-table-shell-scroll">
        <table class="table score-table table-mobile">
            <thead>
                <tr>
                    <th>Pilih</th>
                    <th>Siswa</th>
                    <th>Status</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    @php
                        $isActiveStudent = $student->status === 'Aktif';
                        $hasHistoricalScore = $sessionForm?->scores?->contains('student_id', $student->id) ?? false;
                        $isSelected = in_array($student->id, $selectedStudents, true);
                        $isLockedInactiveScore = ! $isActiveStudent && $hasHistoricalScore;
                        $isChecked = $isLockedInactiveScore || ($isActiveStudent && $isSelected);
                        $scoreValue = $scoreValues[$student->id] ?? '';
                        $statusBadgeClass = $isActiveStudent ? 'status-tag-active' : 'status-tag-inactive';
                    @endphp
                    <tr class="{{ $isChecked ? 'selected' : '' }}">
                        <td class="score-row-select" data-label="Pilih">
                            @if ($isLockedInactiveScore)
                                <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                            @endif
                            <input
                                class="student-toggle"
                                data-target="score-{{ $student->id }}"
                                type="checkbox"
                                name="student_ids[]"
                                value="{{ $student->id }}"
                                @checked($isChecked)
                                @disabled(! $isActiveStudent)
                            >
                        </td>
                        <td class="score-row-student" data-label="Siswa">
                            <p class="student-name">{{ $student->name }}</p>
                            <div class="score-row-student-meta">
                                <span class="badge score-row-inline-status {{ $statusBadgeClass }}">{{ $student->status }}</span>
                            </div>
                        </td>
                        <td class="score-row-status" data-label="Status"><span class="badge {{ $statusBadgeClass }}">{{ $student->status }}</span></td>
                        <td class="score-row-value" data-label="Nilai">
                            <span class="score-row-value-label">Nilai</span>
                            <input
                                id="score-{{ $student->id }}"
                                class="score-input"
                                type="number"
                                name="scores[{{ $student->id }}]"
                                min="0"
                                max="100"
                                value="{{ $scoreValue }}"
                                @disabled(! $isChecked)
                                @readonly($isLockedInactiveScore)
                            >
                            @if (! $isActiveStudent)
                                <p class="score-row-note muted-text">
                                    {{ $isLockedInactiveScore ? 'Nilai lama terkunci karena siswa sudah nonaktif.' : 'Siswa nonaktif tidak bisa dinilai.' }}
                                </p>
                            @endif
                            @error("scores.{$student->id}")
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @error('student_ids')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>
