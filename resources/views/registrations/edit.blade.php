@extends('layouts.app')

@section('title', 'Edit Pendaftaran')
@section('page_heading', 'Edit Data Pendaftaran')
@section('page_description', '')

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Perbarui Data {{ $student->nama }}</h3>
            </div>
        </div>

        <form action="{{ route('registrations.update', $student) }}" method="POST" data-required-alert-form novalidate>
            @csrf
            @method('PUT')
            @include('students._form', ['student' => $student])

            <div class="form-actions">
                <a class="btn btn-secondary" href="{{ route('registrations.index') }}">Kembali</a>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection
