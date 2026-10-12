@extends('layouts.app')

@section('title', 'Tambah Buku')
@section('breadcrumb', 'Tambah Buku')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Katalog perpustakaan</p>
            <h1>Tambah buku</h1>
            <p>Lengkapi informasi buku untuk memasukkannya ke dalam koleksi.</p>
        </div>
    </div>

    @include('books._form', ['book' => null, 'borrowedCopies' => 0])
@endsection
