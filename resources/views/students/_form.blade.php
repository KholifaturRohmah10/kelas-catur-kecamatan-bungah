@php
    $genderValue = old('gender', $student->gender ?? '');
    $selectedGender = match ($genderValue) {
        'Laki' => 'Laki-laki',
        'PR' => 'Perempuan',
        default => $genderValue,
    };
    $statusValue = old('status', $student->status ?? 'Aktif');
    $selectedStatus = $statusValue === 'Nonaktif' ? 'Nonaktif' : 'Aktif';
@endphp

<div class="form-grid">
    @if (isset($student))
        <div class="form-group">
            <label>Kode siswa</label>
            <div class="code-badge">{{ $student->student_code }}</div>
        </div>
    @endif

    <div class="form-group">
        <label for="name">Nama lengkap <span class="required-mark">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $student->name ?? '') }}" placeholder="Contoh: Aditya Nugraha" required>
        @error('name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="gender">Gender <span class="required-mark">*</span></label>
        <select id="gender" name="gender" required>
            <option value="" disabled hidden @selected($selectedGender === '')>Pilih gender</option>
            @foreach (['Laki-laki', 'Perempuan'] as $gender)
                <option value="{{ $gender }}" @selected($selectedGender === $gender)>{{ $gender }}</option>
            @endforeach
        </select>
        @error('gender')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="birth_date">Tanggal lahir <span class="required-mark">*</span></label>
        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', isset($student) && $student->birth_date ? $student->birth_date->format('Y-m-d') : '') }}" required>
        @error('birth_date')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="school_name">Asal sekolah <span class="required-mark">*</span></label>
        <input id="school_name" name="school_name" type="text" value="{{ old('school_name', $student->school_name ?? '') }}" placeholder="Nama sekolah" required>
        @error('school_name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="parent_name">Nama orang tua / wali <span class="required-mark">*</span></label>
        <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name', $student->parent_name ?? '') }}" placeholder="Nama orang tua / wali" required>
        @error('parent_name')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="phone">No. telepon <span class="required-mark">*</span></label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $student->phone ?? '') }}" placeholder="08xxxxxxxxxx" required>
        @error('phone')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="registration_date">Tanggal daftar <span class="required-mark">*</span></label>
        <input id="registration_date" name="registration_date" type="date" value="{{ old('registration_date', isset($student) && $student->registration_date ? $student->registration_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('registration_date')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    @if (isset($student))
        <div class="form-group">
            <label for="status">Status siswa <span class="required-mark">*</span></label>
            <select id="status" name="status" required>
                @foreach (['Aktif', 'Nonaktif'] as $status)
                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    @else
        <div class="form-group">
            <label>Status siswa <span class="required-mark">*</span></label>
            <div class="code-badge">Aktif</div>
            <input type="hidden" name="status" value="Aktif">
        </div>
    @endif

    <div class="form-group full-width">
        <label for="address">Alamat <span class="required-mark">*</span></label>
        <textarea id="address" name="address" placeholder="Alamat lengkap siswa" required>{{ old('address', $student->address ?? '') }}</textarea>
        @error('address')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="notes">Catatan tambahan</label>
        <textarea id="notes" name="notes" placeholder="Catatan pendaftaran, kebutuhan belajar, atau informasi penting lainnya">{{ old('notes', $student->notes ?? '') }}</textarea>
        @error('notes')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>
</div>
