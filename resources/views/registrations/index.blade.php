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
                        <article class="registration-card" onclick="window.location.href='{{ route('students.index', ['search' => $student->kode_siswa]) }}'" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)';">
                            <div class="registration-top">
                                <div>
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
                                </div>
                                <span class="badge {{ $statusBadgeClass }}">{{ $student->status }}</span>
                            </div>

                            <div class="registration-meta-row">
                                <span class="registration-date">Daftar {{ $student->tanggal_daftar->translatedFormat('d M Y') }}</span>
                            </div>

                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
