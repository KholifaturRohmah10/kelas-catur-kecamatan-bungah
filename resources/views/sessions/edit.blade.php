@extends('layouts.app')

@section('title', 'Edit Riwayat Kelas')
@section('page_heading', 'Edit Riwayat Kelas')
@section('page_description', '')

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Perbarui {{ $classSession->title }}</h3>
            </div>
        </div>

        <form action="{{ route('session-history.update', $classSession) }}" method="POST">
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
