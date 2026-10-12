@extends('layouts.app')

@section('title', 'Edit Kategori')
@section('breadcrumb', 'Edit Kategori')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Data katalog</p><h1>Edit kategori</h1><p>Perbarui kategori <strong>{{ $category->name }}</strong>.</p></div>
    </div>
    @include('categories._form', ['category' => $category])
@endsection
