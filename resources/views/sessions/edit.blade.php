@extends('layouts.app')

@section('title', 'Edit Riwayat Kelas')
@section('page_heading', 'Edit Riwayat Kelas')
@section('page_description', '')

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Perbarui {{ $classSession->judul }}</h3>
            </div>
        </div>

        <form action="{{ route('session-history.update', $classSession) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('sessions._form', ['classSession' => $classSession, 'selectedStudents' => $selectedStudents, 'scoreValues' => $scoreValues])

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('session-history.index') }}">Kembali</a>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.student-toggle').forEach(function (checkbox) {
            const scoreInput = document.getElementById(checkbox.dataset.scoreTarget);
            const attendanceInput = document.getElementById(checkbox.dataset.attendanceTarget);
            const row = checkbox.closest('tr');

            const syncState = function () {
                const isLockedAttendance = attendanceInput && attendanceInput.dataset.locked === 'true';
                const isAbsent = attendanceInput && attendanceInput.value === 'absent';

                if (attendanceInput && !isLockedAttendance) {
                    attendanceInput.disabled = !checkbox.checked;
                }

                scoreInput.disabled = !checkbox.checked || isAbsent;
                if (!checkbox.checked || isAbsent) {
                    scoreInput.value = '';
                }
                row.classList.toggle('selected', checkbox.checked);
                row.classList.toggle('attendance-absent', checkbox.checked && isAbsent);
            };

            checkbox.addEventListener('change', syncState);
            if (attendanceInput) {
                attendanceInput.addEventListener('change', syncState);
            }
            syncState();
        });
    </script>
@endsection
