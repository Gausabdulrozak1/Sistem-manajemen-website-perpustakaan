@extends('layouts.app')

@section('title', 'Edit Buku')
@section('breadcrumb', 'Edit Buku')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Katalog perpustakaan</p>
            <h1>Edit buku</h1>
            <p>Perbarui data untuk <strong>{{ $book->title }}</strong>.</p>
        </div>
    </div>

    @include('books._form', ['book' => $book, 'borrowedCopies' => $borrowedCopies])
@endsection
