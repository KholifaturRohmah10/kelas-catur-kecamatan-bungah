@php
    $genderValue = old('jenis_kelamin', $student->jenis_kelamin ?? '');
    $selectedGender = match ($genderValue) {
        'Laki' => 'Laki-laki',
        'PR' => 'Perempuan',
        default => $genderValue,
    };
    $statusValue = old('status', $student->status ?? 'Aktif');
    $selectedStatus = $statusValue === 'Nonaktif' ? 'Nonaktif' : 'Aktif';
@endphp

<div class="form-grid">
    <div class="form-group full-width">
        <label>Kode Siswa</label>
        <input type="text" value="{{ isset($student) ? $student->kode_siswa : ($nextStudentCode ?? '') }}" disabled readonly>
        <p class="form-help">Kode unik ini digunakan wali murid untuk login dan melihat rapot.</p>
    </div>

    <div class="form-group">
        <label for="nama">Nama lengkap <span class="required-mark">*</span></label>
        <input id="nama" name="nama" type="text" value="{{ old('nama', $student->nama ?? '') }}" placeholder="Contoh: Aditya Nugraha" data-uppercase required>
        @error('nama')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="jenis_kelamin">Gender <span class="required-mark">*</span></label>
        <select id="jenis_kelamin" name="jenis_kelamin" required>
            <option value="" disabled hidden @selected($selectedGender === '')>Pilih gender</option>
            @foreach (['Laki-laki', 'Perempuan'] as $gender)
                <option value="{{ $gender }}" @selected($selectedGender === $gender)>{{ $gender }}</option>
            @endforeach
        </select>
        @error('jenis_kelamin')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="tanggal_lahir">Tanggal lahir <span class="required-mark">*</span></label>
        <input id="tanggal_lahir" name="tanggal_lahir" type="date" value="{{ old('tanggal_lahir', isset($student) && $student->tanggal_lahir ? $student->tanggal_lahir->format('Y-m-d') : '') }}" required>
        @error('tanggal_lahir')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="asal_sekolah">Asal sekolah <span class="required-mark">*</span></label>
        <input id="asal_sekolah" name="asal_sekolah" type="text" value="{{ old('asal_sekolah', $student->asal_sekolah ?? '') }}" placeholder="Nama sekolah" data-uppercase required>
        @error('asal_sekolah')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="nama_wali">Nama orang tua / wali <span class="required-mark">*</span></label>
        <input id="nama_wali" name="nama_wali" type="text" value="{{ old('nama_wali', $student->nama_wali ?? '') }}" placeholder="Nama orang tua / wali" data-uppercase required>
        @error('nama_wali')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="telepon">No. telepon <span class="required-mark">*</span></label>
        <input id="telepon" name="telepon" type="text" value="{{ old('telepon', $student->telepon ?? '') }}" placeholder="08xxxxxxxxxx" required>
        @error('telepon')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="tanggal_daftar">Tanggal daftar <span class="required-mark">*</span></label>
        <input id="tanggal_daftar" name="tanggal_daftar" type="date" value="{{ old('tanggal_daftar', isset($student) && $student->tanggal_daftar ? $student->tanggal_daftar->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('tanggal_daftar')
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
        <label for="alamat">Alamat <span class="required-mark">*</span></label>
        <textarea id="alamat" name="alamat" placeholder="Alamat lengkap siswa" data-uppercase required>{{ old('alamat', $student->alamat ?? '') }}</textarea>
        @error('alamat')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group full-width">
        <label for="catatan">Catatan tambahan</label>
        <textarea id="catatan" name="catatan" placeholder="Catatan pendaftaran, kebutuhan belajar, atau informasi penting lainnya">{{ old('catatan', $student->catatan ?? '') }}</textarea>
        @error('catatan')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>
</div>
