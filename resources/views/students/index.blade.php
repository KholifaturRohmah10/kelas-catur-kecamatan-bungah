@extends('layouts.app')

@section('title', 'Data Siswa')
@section('page_heading', 'Data Siswa')
@section('page_description', '')

@section('content')
    <section class="toolbar">
        <div>
            <strong>{{ $students->count() }} siswa</strong>
        </div>

        <form action="{{ route('students.index') }}" method="GET">
            <div class="table-actions">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / sekolah">
                <button class="btn btn-primary" type="submit">Cari</button>
                @if ($search !== '')
                    <a class="btn btn-light" href="{{ route('students.index') }}">Reset</a>
                @endif
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Informasi Umum Siswa</h3>
            </div>
        </div>

        @if ($students->isEmpty())
            <div class="empty-state">
                Data siswa belum tersedia. Silakan tambah data dari menu Daftar Kelas Catur terlebih dahulu.
            </div>
        @else
            <div class="table-shell table-shell-scroll">
                <table class="table table-mobile students-table-mobile">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Gender</th>
                            <th>Tanggal lahir</th>
                            <th>Status</th>
                            <th>Kontak</th>
                            <th>Tanggal daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php($statusBadgeClass = $student->status === 'Aktif' ? 'status-tag-active' : 'status-tag-inactive')
                            <tr data-collapsed="true">
                                <td class="student-primary-cell" data-label="Siswa">
                                    <button class="student-row-toggle" type="button" aria-expanded="false" data-student-toggle>
                                        <span class="student-row-toggle-copy">
                                            <p class="student-name" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span>{{ $student->nama }}</span>
                                                @if ($student->catatan)
                                                    <span title="{{ $student->catatan }}" style="font-size: 0.82rem; color: #ea580c; font-weight: 700; font-style: italic; cursor: help;">* {{ Str::limit($student->catatan, 35) }}</span>
                                                @endif
                                            </p>
                                            <p class="student-meta" style="font-weight: 500; color: var(--primary); display: flex; align-items: center; gap: 4px;">
                                                Kode: <span class="student-code-text">{{ $student->kode_siswa }}</span>
                                                <span class="copy-code-btn" style="cursor: pointer; color: #64748b; padding: 2px; display: inline-flex;" title="Salin Kode" onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText('{{ $student->kode_siswa }}').then(() => { const original = this.innerHTML; this.innerHTML = '<span style=\'color:var(--primary)\'>✓</span>'; setTimeout(() => this.innerHTML = original, 1500); });">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                </span>
                                            </p>
                                            @if ($student->asal_sekolah)
                                                <p class="student-meta">{{ $student->asal_sekolah }}</p>
                                            @endif
                                        </span>
                                        <span class="student-row-toggle-hint" aria-hidden="true">
                                            <span class="student-row-toggle-open">Show more</span>
                                            <span class="student-row-toggle-close">Show less</span>
                                        </span>
                                    </button>
                                </td>
                                <td class="student-row-detail-cell" data-label="Gender">{{ $student->jenis_kelamin }}</td>
                                <td class="student-row-detail-cell" data-label="Tanggal lahir">{{ $student->tanggal_lahir?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="student-row-detail-cell" data-label="Status"><span class="badge {{ $statusBadgeClass }}">{{ $student->status }}</span></td>
                                <td class="student-row-detail-cell" data-label="Kontak">
                                    <p class="student-name">{{ $student->telepon ?: '-' }}</p>
                                    <p class="student-meta">{{ $student->nama_wali ?: 'Wali belum diisi' }}</p>
                                    <p class="student-meta student-address">{{ $student->alamat ?: 'Alamat belum diisi' }}</p>
                                </td>
                                <td class="student-row-detail-cell" data-label="Tanggal daftar">{{ $student->tanggal_daftar?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="student-row-detail-cell" data-label="Aksi">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; min-width: 80px; max-width: 100px;">
                                        <a class="btn btn-light" href="{{ route('students.edit', $student) }}" title="Edit" style="width: 100%; text-align: center; box-sizing: border-box; display: flex; justify-content: center; align-items: center; padding: 0.5rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        </a>
                                        <form action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm('Hapus data siswa ini? Nilai yang berkaitan juga akan ikut terhapus.')" style="margin: 0; width: 100%; display: flex;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit" title="Hapus" style="width: 100%; box-sizing: border-box; display: flex; justify-content: center; align-items: center; padding: 0.5rem;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </button>
                                        </form>
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

            document.querySelectorAll('[data-student-toggle]').forEach(function (button) {
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
