@extends('layouts.app')

@section('title', 'Koleksi Buku')
@section('breadcrumb', 'Koleksi Buku')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Katalog perpustakaan</p>
            <h1>Koleksi buku</h1>
            <p>Kelola informasi judul, penulis, dan ketersediaan koleksi.</p>
        </div>
        <a class="button" href="{{ route('books.create') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah buku
        </a>
    </div>

    @if (session('success'))
        <div class="notice" role="status">{{ session('success') }}</div>
    @endif

    <section class="content-card">
        <div class="card-heading catalog-heading">
            <div><h2>Daftar buku</h2><p>{{ number_format($books->total(), 0, ',', '.') }} judul terdaftar di katalog</p></div>
            <form class="search-form" action="{{ route('books.index') }}" method="GET" role="search">
                <label class="visually-hidden" for="book-search">Cari katalog</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                <input id="book-search" type="search" name="search" value="{{ $search }}" placeholder="Cari judul, penulis, kode..." maxlength="100">
                <label class="visually-hidden" for="book-category">Saring kategori</label>
                <select id="book-category" name="category_id" aria-label="Kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $category->id === $categoryId)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button class="search-submit" type="submit">Cari</button>
            </form>
        </div>
        @if ($books->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Kode</th><th>Judul buku</th><th>Kategori</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>Stok</th><th>Lokasi rak</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($books as $book)
                            <tr>
                                <td><span class="book-code">{{ $book->book_code }}</span></td>
                                <td class="book-title">{{ $book->title }}</td>
                                <td>{{ $book->category?->name ?? '—' }}</td>
                                <td>{{ $book->author }}</td>
                                <td>{{ $book->publisher }}</td>
                                <td>{{ $book->publication_year ?: '—' }}</td>
                                <td><span class="stock-pill {{ $book->available_stock < 1 ? 'is-empty' : '' }}">{{ number_format($book->available_stock, 0, ',', '.') }} / {{ number_format($book->total_stock, 0, ',', '.') }}</span></td>
                                <td>{{ $book->shelf_location ?: '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="row-link" href="{{ route('books.show', $book) }}" aria-label="Lihat {{ $book->title }}">Lihat</a>
                                        <a class="row-link" href="{{ route('books.edit', $book) }}" aria-label="Edit {{ $book->title }}">Edit</a>
                                        <form action="{{ route('books.destroy', $book) }}" method="POST" data-confirm-delete="Hapus buku dengan kode {{ $book->book_code }} dari katalog?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="row-link row-link-danger" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($books->hasPages())
                <div class="pagination-wrap">{{ $books->links() }}</div>
            @endif
        @else
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19M9 8h6"/></svg></span>
                @if ($search !== '')
                    <h3>Buku tidak ditemukan</h3>
                    <p>Tidak ada hasil untuk pencarian “{{ $search }}”. Periksa ejaan atau hapus kata kunci.</p>
                    <a class="button button-secondary" href="{{ route('books.index') }}">Hapus pencarian</a>
                @else
                    <h3>Belum ada buku di katalog</h3>
                    <p>Mulai dengan mencatat buku pertama beserta kode, penulis, stok, dan lokasi raknya.</p>
                    <a class="button" href="{{ route('books.create') }}">Tambah buku pertama</a>
                @endif
            </div>
        @endif
    </section>
@endsection
