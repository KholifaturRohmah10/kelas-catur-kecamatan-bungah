@extends('layouts.app')

@section('title', 'Edit Siswa')
@section('page_heading', 'Edit Data Siswa')
@section('page_description', '')

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Perbarui Data {{ $student->name }}</h3>
            </div>
        </div>

        <form action="{{ route('students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')
            @include('students._form', ['student' => $student])

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('students.index') }}">Kembali</a>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection
