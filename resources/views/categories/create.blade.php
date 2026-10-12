@extends('layouts.app')

@section('title', 'Tambah Kategori')
@section('breadcrumb', 'Tambah Kategori')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Data katalog</p><h1>Tambah kategori</h1><p>Kelompokkan buku agar lebih mudah ditemukan.</p></div>
    </div>
    @include('categories._form', ['category' => null])
@endsection
