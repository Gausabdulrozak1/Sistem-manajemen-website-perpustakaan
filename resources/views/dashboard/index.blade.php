@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Ringkasan perpustakaan</p>
            <h1>Selamat datang di RuangBaca</h1>
            <p>Pantau koleksi dan ketersediaan buku perpustakaan Anda.</p>
        </div>
        <a class="button" href="{{ route('books.create') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Tambah buku
        </a>
    </div>

    <section class="stats-grid" aria-label="Statistik perpustakaan">
        <article class="stat-card">
            <div class="stat-top"><span class="stat-label">Judul dalam koleksi</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19M9 8h6M9 11h5"/></svg></span></div>
            <div class="stat-value">{{ number_format($totalTitles, 0, ',', '.') }}</div>
            <div class="stat-foot">Jumlah jenis buku yang tercatat</div>
        </article>
        <article class="stat-card">
            <div class="stat-top"><span class="stat-label">Total eksemplar</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5M8 5.2l8 4.5"/></svg></span></div>
            <div class="stat-value">{{ number_format($totalCopies, 0, ',', '.') }}</div>
            <div class="stat-foot">Seluruh salinan dari semua judul</div>
        </article>
        <article class="stat-card">
            <div class="stat-top"><span class="stat-label">Buku tersedia</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5 9.2 17 19 7"/><circle cx="12" cy="12" r="9"/></svg></span></div>
            <div class="stat-value">{{ number_format($availableCopies, 0, ',', '.') }}</div>
            <div class="stat-foot">Siap dipinjam atau dibaca</div>
        </article>
        <article class="stat-card">
            <div class="stat-top"><span class="stat-label">Anggota terdaftar</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M16 5.2a3.5 3.5 0 0 1 0 6.6M17 14a5.6 5.6 0 0 1 4.2 5.4"/></svg></span></div>
            <div class="stat-value">{{ number_format($totalMembers, 0, ',', '.') }}</div>
            <div class="stat-foot">Pembaca yang tercatat di sistem</div>
        </article>
        <article class="stat-card">
            <div class="stat-top"><span class="stat-label">Kategori koleksi</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20.5 13.2-7.3 7.3L3 10.3V3h7.3l10.2 10.2Z"/><circle cx="7.5" cy="7.5" r="1"/></svg></span></div>
            <div class="stat-value">{{ number_format($totalCategories, 0, ',', '.') }}</div>
            <div class="stat-foot">Pengelompokan dalam katalog</div>
        </article>
    </section>

    <section class="circulation-overview" aria-label="Ringkasan peminjaman">
        <article class="circulation-card">
            <span class="circulation-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19"/></svg></span>
            <div><span class="circulation-label">Sedang dipinjam</span><strong>{{ number_format($activeLoans, 0, ',', '.') }}</strong><small>transaksi belum kembali</small></div>
            <a href="{{ route('loans.index', ['status' => 'active']) }}">Lihat transaksi <span aria-hidden="true">→</span></a>
        </article>
        <article class="circulation-card circulation-card-overdue">
            <span class="circulation-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div><span class="circulation-label">Melewati tenggat</span><strong>{{ number_format($overdueLoans, 0, ',', '.') }}</strong><small>perlu ditindaklanjuti</small></div>
            <a href="{{ route('loans.index', ['status' => 'overdue']) }}">Lihat transaksi <span aria-hidden="true">→</span></a>
        </article>
    </section>

    <section class="content-card">
        <div class="card-heading">
            <div><h2>Buku yang baru ditambahkan</h2><p>Lima entri terakhir dalam katalog.</p></div>
            <a class="text-link" href="{{ route('books.index') }}">Lihat semua buku&nbsp; →</a>
        </div>
        @if ($recentBooks->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Kode</th><th>Judul buku</th><th>Kategori</th><th>Penulis</th><th>Tahun</th><th>Stok tersedia</th></tr></thead>
                    <tbody>
                        @foreach ($recentBooks as $book)
                            <tr>
                                <td><span class="book-code">{{ $book->book_code }}</span></td>
                                <td class="book-title">{{ $book->title }}</td>
                                <td>{{ $book->category?->name ?? '—' }}</td>
                                <td>{{ $book->author }}</td>
                                <td>{{ $book->publication_year ?: '—' }}</td>
                                <td><span class="stock-pill">{{ number_format($book->available_stock, 0, ',', '.') }} tersedia</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19M9 8h6"/></svg></span>
                <h3>Koleksi Anda masih kosong</h3>
                <p>Tambahkan buku pertama agar ringkasan koleksi dan ketersediaan tampil di sini.</p>
                <a class="button" href="{{ route('books.create') }}">Tambah buku pertama</a>
            </div>
        @endif
    </section>
@endsection
