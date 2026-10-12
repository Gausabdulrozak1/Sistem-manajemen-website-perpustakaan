@extends('layouts.app')

@section('title', 'Edit pengguna')
@section('breadcrumb', 'Edit pengguna')

@section('content')
    <div class="page-heading"><div><p class="eyebrow">Administrasi akses</p><h1>Edit pengguna</h1><p>Perbarui profil atau tingkat akses akun.</p></div><a class="button button-secondary" href="{{ route('users.index') }}">Kembali ke pengguna</a></div>
    @include('users._form', ['user' => $user, 'action' => route('users.update', $user), 'method' => 'PUT'])
@endsection
