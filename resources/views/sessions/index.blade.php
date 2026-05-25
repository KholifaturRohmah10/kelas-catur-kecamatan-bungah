@extends('layouts.app')

@section('title', 'Jadwal Kelas')
@section('page_heading', 'Jadwal Kelas')
@section('page_description', '')

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Input Pertemuan dan Nilai</h3>
            </div>
            <a class="btn btn-secondary" href="{{ route('session-history.index') }}">Buka Riwayat Kelas</a>
        </div>

        @if ($students->where('status', 'Aktif')->isEmpty())
            <div class="empty-state">
                Belum ada siswa aktif yang bisa dinilai. Aktifkan siswa terlebih dahulu dari menu Data Siswa atau Daftar Kelas Catur.
            </div>
        @else
            <form action="{{ route('sessions.store') }}" method="POST">
                @csrf
                @include('sessions._form')

                <div class="spacer-top">
                    <button class="btn btn-primary" type="submit">Simpan Data</button>
                </div>
            </form>
        @endif
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.student-toggle').forEach(function (checkbox) {
            const targetId = checkbox.dataset.target;
            const scoreInput = document.getElementById(targetId);
            const row = checkbox.closest('tr');

            const syncState = function () {
                scoreInput.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    scoreInput.value = '';
                }
                row.classList.toggle('selected', checkbox.checked);
            };

            checkbox.addEventListener('change', syncState);
            syncState();
        });
    </script>
@endsection
