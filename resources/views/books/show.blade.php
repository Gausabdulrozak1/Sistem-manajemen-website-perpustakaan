@extends('layouts.app')

@section('title', $book->title)
@section('breadcrumb', 'Detail Buku')

@section('content')
    @if (session('success'))
        <div class="notice" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="notice notice-error" role="alert">{{ session('error') }}</div>
    @endif
    <div class="page-heading detail-heading">
        <div>
            <p class="eyebrow">Detail koleksi · {{ $book->book_code }}</p>
            <h1>{{ $book->title }}</h1>
            <p>Ditulis oleh {{ $book->author }}</p>
        </div>
        <div class="heading-actions">
            <a class="button button-secondary" href="{{ route('books.index') }}">Kembali ke katalog</a>
            <a class="button" href="{{ route('books.edit', $book) }}">Edit buku</a>
        </div>
    </div>

    <section class="content-card detail-card">
        <div class="detail-banner">
            <span class="detail-book-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M6 8.5c3.8-.9 7.2-.2 10 2.1v14c-2.8-2.3-6.2-3-10-2.1v-14Zm20 0c-3.8-.9-7.2-.2-10 2.1v14c2.8-2.3 6.2-3 10-2.1v-14Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M16 10.6v14" stroke="currentColor" stroke-width="1.6"/></svg></span>
            <div><span class="book-code">{{ $book->book_code }}</span><p>Ditambahkan {{ $book->created_at?->locale('id')->translatedFormat('d F Y') ?? '—' }}</p></div>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><span>Penulis</span><strong>{{ $book->author }}</strong></div>
            <div class="detail-item"><span>Penerbit</span><strong>{{ $book->publisher }}</strong></div>
            <div class="detail-item"><span>Tahun terbit</span><strong>{{ $book->publication_year ?: 'Belum dicatat' }}</strong></div>
        <div class="detail-item"><span>Kategori</span><strong>{{ $book->category?->name ?? 'Belum dikategorikan' }}</strong></div>
        <div class="detail-item"><span>Lokasi rak</span><strong>{{ $book->shelf_location ?: 'Belum ditentukan' }}</strong></div>
            <div class="detail-item"><span>Total eksemplar</span><strong>{{ number_format($book->total_stock, 0, ',', '.') }}</strong></div>
            <div class="detail-item"><span>Tersedia</span><strong class="detail-available">{{ number_format($book->available_stock, 0, ',', '.') }} buku</strong></div>
        </div>
    </section>
@endsection
