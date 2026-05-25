@extends('layouts.app')

@section('title', 'Daftar Kelas Catur')
@section('page_heading', 'Daftar Kelas Catur')
@section('page_description', '')

@section('content')
    <section class="toolbar">
        <div>
            <strong>{{ $students->count() }} pendaftaran minggu ini</strong>
        </div>
    </section>

    <section class="two-column registrations-layout">
        <article class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Form Siswa</h3>
                </div>
            </div>

            <form action="{{ route('registrations.store') }}" method="POST" data-required-alert-form novalidate>
                @csrf
                @include('students._form')

                <div class="spacer-top">
                    <button class="btn btn-primary" type="submit">Simpan Pendaftaran</button>
                </div>
            </form>
        </article>

        <article class="panel registration-week-panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Pendaftaran Minggu Ini</h3>
                    <p class="student-meta">{{ $startOfWeek->translatedFormat('d M') }} - {{ $endOfWeek->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            @if ($students->isEmpty())
                <div class="empty-state">
                    Belum ada pendaftaran pada minggu ini.
                </div>
            @else
                <div class="registration-list">
                    @foreach ($students as $student)
                        @php($statusBadgeClass = $student->status === 'Aktif' ? 'status-tag-active' : 'status-tag-inactive')
                        <article class="registration-card">
                            <div class="registration-top">
                                <div>
                                    <p class="student-name">{{ $student->name }}</p>
                                    @if ($student->school_name)
                                        <p class="student-meta">{{ $student->school_name }}</p>
                                    @endif
                                </div>
                                <span class="badge {{ $statusBadgeClass }}">{{ $student->status }}</span>
                            </div>

                            <div class="registration-meta-row">
                                <span class="registration-date">Daftar {{ $student->registration_date->translatedFormat('d M Y') }}</span>
                            </div>

                            <div class="registration-actions">
                                <a class="btn btn-light" href="{{ route('registrations.edit', $student) }}">Edit</a>
                                <form class="inline-form" action="{{ route('registrations.destroy', $student) }}" method="POST" onsubmit="return confirm('Hapus data siswa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
