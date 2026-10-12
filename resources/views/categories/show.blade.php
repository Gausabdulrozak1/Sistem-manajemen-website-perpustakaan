@extends('layouts.app')

@section('title', $category->name)
@section('breadcrumb', 'Detail Kategori')

@section('content')
    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    <div class="page-heading detail-heading">
        <div><p class="eyebrow">Kategori koleksi</p><h1>{{ $category->name }}</h1><p>{{ $category->description ?: 'Belum ada deskripsi untuk kategori ini.' }}</p></div>
        <div class="heading-actions">
            <a class="button button-secondary" href="{{ route('categories.index') }}">Kembali</a>
            <a class="button" href="{{ route('categories.edit', $category) }}">Edit kategori</a>
        </div>
    </div>
    <section class="content-card">
        <div class="card-heading"><div><h2>Buku dalam kategori ini</h2><p>{{ number_format($books->total(), 0, ',', '.') }} judul</p></div></div>
        @if ($books->isNotEmpty())
            <div class="table-wrap"><table class="data-table">
                <thead><tr><th>Kode</th><th>Judul buku</th><th>Penulis</th><th>Stok tersedia</th><th></th></tr></thead>
                <tbody>@foreach ($books as $book)
                    <tr><td><span class="book-code">{{ $book->book_code }}</span></td><td class="book-title">{{ $book->title }}</td><td>{{ $book->author }}</td><td>{{ number_format($book->available_stock, 0, ',', '.') }} / {{ number_format($book->total_stock, 0, ',', '.') }}</td><td><a class="row-link" href="{{ route('books.show', $book) }}">Detail</a></td></tr>
                @endforeach</tbody>
            </table></div>
            @if ($books->hasPages()) <div class="pagination-wrap">{{ $books->links() }}</div> @endif
        @else
            <div class="empty-state"><h3>Belum ada buku di kategori ini</h3><p>Pilih kategori ini saat menambahkan atau mengedit data buku.</p><a class="button" href="{{ route('books.create') }}">Tambah buku</a></div>
        @endif
    </section>
@endsection
