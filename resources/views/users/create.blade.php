@extends('layouts.app')

@section('title', 'Tambah pengguna')
@section('breadcrumb', 'Pengguna baru')

@section('content')
    <div class="page-heading"><div><p class="eyebrow">Administrasi akses</p><h1>Tambah pengguna</h1><p>Buat akun admin atau petugas untuk perpustakaan.</p></div><a class="button button-secondary" href="{{ route('users.index') }}">Kembali ke pengguna</a></div>
    @include('users._form', ['user' => null, 'action' => route('users.store'), 'method' => 'POST'])
@endsection
