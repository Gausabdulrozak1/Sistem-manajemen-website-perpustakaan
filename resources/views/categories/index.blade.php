@extends('layouts.app')

@section('title', 'Kategori')
@section('breadcrumb', 'Kategori')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Data katalog</p><h1>Kategori buku</h1><p>Atur pengelompokan untuk membantu pengunjung menemukan bacaan.</p></div>
        <a class="button" href="{{ route('categories.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah kategori</a>
    </div>
    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    <section class="content-card">
        <div class="card-heading catalog-heading">
            <div><h2>Daftar kategori</h2><p>{{ number_format($categories->total(), 0, ',', '.') }} kategori tercatat</p></div>
            <form class="search-form" action="{{ route('categories.index') }}" method="GET" role="search">
                <label class="visually-hidden" for="category-search">Cari kategori</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                <input id="category-search" type="search" name="search" value="{{ $search }}" placeholder="Cari kategori..." maxlength="100">
                <button class="search-submit" type="submit">Cari</button>
            </form>
        </div>
        @if ($categories->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Nama kategori</th><th>Deskripsi</th><th>Jumlah judul</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td class="book-title">{{ $category->name }}</td>
                                <td class="description-cell">{{ $category->description ?: '—' }}</td>
                                <td>{{ number_format($category->books_count, 0, ',', '.') }} buku</td>
                                <td><div class="row-actions">
                                    <a class="row-link" href="{{ route('categories.show', $category) }}">Lihat</a>
                                    <a class="row-link" href="{{ route('categories.edit', $category) }}">Edit</a>
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" data-confirm-delete="Hapus kategori {{ $category->name }}? Buku dalam kategori ini akan tetap tersimpan.">
                                        @csrf @method('DELETE')
                                        <button class="row-link row-link-danger" type="submit">Hapus</button>
                                    </form>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($categories->hasPages()) <div class="pagination-wrap">{{ $categories->links() }}</div> @endif
        @else
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m20.5 13.2-7.3 7.3L3 10.3V3h7.3l10.2 10.2Z"/><circle cx="7.5" cy="7.5" r="1"/></svg></span>
                <h3>{{ $search !== '' ? 'Kategori tidak ditemukan' : 'Belum ada kategori' }}</h3>
                <p>{{ $search !== '' ? 'Coba kata kunci lain atau hapus pencarian.' : 'Buat kategori pertama, misalnya Fiksi, Sejarah, atau Sains.' }}</p>
                @if ($search !== '')
                    <a class="button button-secondary" href="{{ route('categories.index') }}">Hapus pencarian</a>
                @else
                    <a class="button" href="{{ route('categories.create') }}">Tambah kategori</a>
                @endif
            </div>
        @endif
    </section>
@endsection
