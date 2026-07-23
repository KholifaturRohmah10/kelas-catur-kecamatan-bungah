@php
    $sessionForm = $classSession ?? null;
    $selectedStudents = collect(old('siswa_ids', $selectedStudents ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $attendanceValues = old('kehadiran', $attendanceValues ?? []);
    $scoreValues = old('nilai', $scoreValues ?? []);
    $activeStudentCount = $students->where('status', 'Aktif')->count();
    $hasInactiveStudents = $students->contains(fn ($student) => $student->status !== 'Aktif');
    $hasLockedInactiveScores = $sessionForm?->scores?->contains(fn ($score) => $score->student && $score->student->status !== 'Aktif') ?? false;
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="judul">Judul pertemuan <span class="required-mark">*</span></label>
        <input id="judul" name="judul" type="text" value="{{ old('judul', $sessionForm?->judul ?? '') }}" placeholder="Contoh: Taktik Pembukaan dan Kontrol Tengah" data-uppercase required>
        @error('judul')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="tanggal">Tanggal <span class="required-mark">*</span></label>
        <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', $sessionForm?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        @error('tanggal')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="file_materi">File materi @if ($sessionForm === null) <span class="required-mark">*</span> @endif</label>
        <input id="file_materi" name="file_materi" type="file" accept=".pdf,.ppt,.pptx" @required($sessionForm === null)>
        <p class="form-help">Upload file materi dalam format PDF, PPT, atau PPTX. Maksimal 20 MB.</p>

        @if ($sessionForm?->hasMaterialFile())
            <div class="material-upload-card">
                <div>
                    <strong class="material-upload-title">File tersimpan</strong>
                    <p class="material-upload-copy">{{ $sessionForm->materialDisplayName() }}</p>
                </div>
                <div class="material-upload-actions">
                    @if ($sessionForm->materialTypeLabel())
                        <span class="chip">{{ $sessionForm->materialTypeLabel() }}</span>
                    @endif
                    <a class="btn btn-secondary" href="{{ route('sessions.material-file', $sessionForm) }}" target="_blank" rel="noopener">Buka File</a>
                </div>
            </div>
        @elseif ($sessionForm?->hasLegacyMaterialText())
            <div class="material-upload-card material-upload-card-legacy">
                <div>
                    <strong class="material-upload-title">Materi lama masih berupa teks</strong>
                    <p class="material-upload-copy">{{ $sessionForm->material }}</p>
                </div>
            </div>
        @endif

        @error('file_materi')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="catatan">Catatan tambahan</label>
        <textarea id="catatan" name="catatan" placeholder="Opsional: catatan instruktur, fokus evaluasi, atau kebutuhan tindak lanjut">{{ old('catatan', $sessionForm?->catatan ?? '') }}</textarea>
        @error('catatan')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="spacer-top">
    <div class="panel-header">
        <div>
            <h4 class="panel-title">Pilih Siswa, Tandai Kehadiran, dan Isi Nilai</h4>
            @if ($hasInactiveStudents)
                <p class="score-table-note muted-text">
                    Siswa nonaktif tidak bisa dipilih untuk penilaian baru.
                    @if ($hasLockedInactiveScores)
                        Data lama yang sudah tersimpan tetap dikunci.
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
                    <th>Kehadiran</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    @php
                        $isActiveStudent = $student->status === 'Aktif';
                        $hasHistoricalScore = $sessionForm?->scores?->contains('siswa_id', $student->id) ?? false;
                        $isSelected = in_array($student->id, $selectedStudents, true);
                        $isLockedInactiveScore = ! $isActiveStudent && $hasHistoricalScore;
                        $isChecked = $isLockedInactiveScore || ($isActiveStudent && $isSelected);
                        $attendanceValue = $attendanceValues[$student->id] ?? ($isLockedInactiveScore ? ($sessionForm?->scores?->firstWhere('siswa_id', $student->id)?->status_kehadiran ?? 'present') : 'present');
                        $scoreValue = $scoreValues[$student->id] ?? '';
                        $statusBadgeClass = $isActiveStudent ? 'status-tag-active' : 'status-tag-inactive';
                    @endphp
                    <tr class="{{ $isChecked ? 'selected' : '' }} {{ $attendanceValue === 'absent' ? 'attendance-absent' : '' }}">
                        <td class="score-row-select" data-label="Pilih">
                            @if ($isLockedInactiveScore)
                                <input type="hidden" name="siswa_ids[]" value="{{ $student->id }}">
                                <input type="hidden" name="kehadiran[{{ $student->id }}]" value="{{ $attendanceValue }}">
                            @endif
                            <input
                                class="student-toggle"
                                data-score-target="score-{{ $student->id }}"
                                data-attendance-target="attendance-{{ $student->id }}"
                                type="checkbox"
                                name="siswa_ids[]"
                                value="{{ $student->id }}"
                                @checked($isChecked)
                                @disabled(! $isActiveStudent)
                            >
                        </td>
                        <td class="score-row-student" data-label="Siswa">
                            <p class="student-name">{{ $student->nama }}</p>
                            <div class="score-row-student-meta">
                                <span class="badge score-row-inline-status {{ $statusBadgeClass }}">{{ $student->status }}</span>
                            </div>
                        </td>
                        <td class="score-row-status" data-label="Status"><span class="badge {{ $statusBadgeClass }}">{{ $student->status }}</span></td>
                        <td class="score-row-attendance" data-label="Kehadiran">
                            <select
                                id="attendance-{{ $student->id }}"
                                class="attendance-select"
                                name="kehadiran[{{ $student->id }}]"
                                @disabled(! $isChecked || $isLockedInactiveScore)
                                data-locked="{{ $isLockedInactiveScore ? 'true' : 'false' }}"
                            >
                                <option value="present" @selected($attendanceValue === 'present')>Hadir</option>
                                <option value="absent" @selected($attendanceValue === 'absent')>Tidak Masuk</option>
                            </select>
                            @error("kehadiran.{$student->id}")
                                <div class="field-error" style="margin-top: 4px; display: block;">{{ $message }}</div>
                            @enderror
                        </td>
                        <td class="score-row-value" data-label="Nilai">
                            <span class="score-row-value-label">Nilai</span>
                            <input
                                id="score-{{ $student->id }}"
                                class="score-input"
                                type="number"
                                name="nilai[{{ $student->id }}]"
                                min="0"
                                max="100"
                                value="{{ $scoreValue }}"
                                style="min-width: 92px; flex-shrink: 0;"
                                @disabled(! $isChecked || $attendanceValue === 'absent')
                                @readonly($isLockedInactiveScore)
                            >
                            @if ($attendanceValue === 'absent')
                                <p class="score-row-note muted-text">
                                    Materi tetap tercatat, tetapi siswa ditandai tidak masuk.
                                </p>
                            @elseif (! $isActiveStudent)
                                <p class="score-row-note muted-text">
                                    {{ $isLockedInactiveScore ? 'Data lama terkunci karena siswa sudah nonaktif.' : 'Siswa nonaktif tidak bisa dinilai.' }}
                                </p>
                            @endif
                            @error("nilai.{$student->id}")
                                <div class="field-error" style="margin-top: 4px; display: block;">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @error('siswa_ids')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>
