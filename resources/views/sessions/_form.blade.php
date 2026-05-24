@php
    $sessionForm = $classSession ?? null;
    $selectedStudents = collect(old('student_ids', $selectedStudents ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $scoreValues = old('scores', $scoreValues ?? []);
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="title">Judul pertemuan</label>
        <input id="title" name="title" type="text" value="{{ old('title', $sessionForm?->title ?? '') }}" placeholder="Contoh: Taktik Pembukaan dan Kontrol Tengah" required>
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
        </div>
        <span class="badge">{{ $students->count() }} siswa tersedia</span>
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
                        $isSelected = in_array($student->id, $selectedStudents, true);
                        $scoreValue = $scoreValues[$student->id] ?? '';
                    @endphp
                    <tr class="{{ $isSelected ? 'selected' : '' }}">
                        <td class="score-row-select" data-label="Pilih">
                            <input
                                class="student-toggle"
                                data-target="score-{{ $student->id }}"
                                type="checkbox"
                                name="student_ids[]"
                                value="{{ $student->id }}"
                                @checked($isSelected)
                            >
                        </td>
                        <td class="score-row-student" data-label="Siswa">
                            <p class="student-name">{{ $student->name }}</p>
                            <div class="score-row-student-meta">
                                <p class="student-meta">{{ $student->student_code }}</p>
                                <span class="badge score-row-inline-status">{{ $student->status }}</span>
                            </div>
                        </td>
                        <td class="score-row-status" data-label="Status"><span class="badge">{{ $student->status }}</span></td>
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
                                @disabled(! $isSelected)
                            >
                            @error("scores.{$student->id}")
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
