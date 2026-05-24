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
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / kode / sekolah">
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
                            <th>Status</th>
                            <th>Kontak</th>
                            <th>Tanggal daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr data-collapsed="true">
                                <td class="student-primary-cell" data-label="Siswa">
                                    <button class="student-row-toggle" type="button" aria-expanded="false" data-student-toggle>
                                        <span class="student-row-toggle-copy">
                                            <p class="student-name">{{ $student->name }}</p>
                                            <p class="student-meta">
                                                {{ $student->student_code }}
                                                {{ $student->school_name ? ' - '.$student->school_name : '' }}
                                            </p>
                                        </span>
                                        <span class="student-row-toggle-hint" aria-hidden="true">
                                            <span class="student-row-toggle-open">Show more</span>
                                            <span class="student-row-toggle-close">Show less</span>
                                        </span>
                                    </button>
                                </td>
                                <td class="student-row-detail-cell" data-label="Gender">{{ $student->gender }}</td>
                                <td class="student-row-detail-cell" data-label="Status"><span class="badge">{{ $student->status }}</span></td>
                                <td class="student-row-detail-cell" data-label="Kontak">
                                    <p class="student-name">{{ $student->phone ?: '-' }}</p>
                                    <p class="student-meta">{{ $student->parent_name ?: 'Wali belum diisi' }}</p>
                                    <p class="student-meta student-address">{{ $student->address ?: 'Alamat belum diisi' }}</p>
                                </td>
                                <td class="student-row-detail-cell" data-label="Tanggal daftar">{{ $student->registration_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="student-row-detail-cell" data-label="Aksi">
                                    <div class="table-actions">
                                        <a class="btn btn-light" href="{{ route('students.edit', $student) }}">Edit</a>
                                        <form class="inline-form" action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm('Hapus data siswa ini? Nilai yang berkaitan juga akan ikut terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">Hapus</button>
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
