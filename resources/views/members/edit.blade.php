@extends('layouts.app')

@section('title', 'Edit Anggota')
@section('breadcrumb', 'Edit Anggota')

@section('content')
    <div class="page-heading"><div><p class="eyebrow">Data anggota</p><h1>Edit anggota</h1><p>Perbarui data untuk <strong>{{ $member->name }}</strong>.</p></div></div>
    @include('members._form', ['member' => $member])
@endsection
