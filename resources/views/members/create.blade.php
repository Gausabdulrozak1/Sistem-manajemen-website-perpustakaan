@extends('layouts.app')

@section('title', 'Tambah Anggota')
@section('breadcrumb', 'Tambah Anggota')

@section('content')
    <div class="page-heading"><div><p class="eyebrow">Data anggota</p><h1>Daftarkan anggota</h1><p>Catat informasi pembaca dan status keanggotaannya.</p></div></div>
    @include('members._form', ['member' => null])
@endsection
