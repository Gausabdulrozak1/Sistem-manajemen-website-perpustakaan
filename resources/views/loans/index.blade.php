@extends('layouts.app')

@section('title', 'Sirkulasi buku')
@section('breadcrumb', 'Sirkulasi buku')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Layanan peminjaman</p><h1>Sirkulasi buku</h1><p>Catat peminjaman, pantau tenggat, dan proses pengembalian.</p></div>
        <a class="button" href="{{ route('loans.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Catat peminjaman</a>
    </div>

    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="notice notice-error" role="alert">{{ session('error') }}</div> @endif

    <section class="stats-grid loan-stats" aria-label="Ringkasan sirkulasi">
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Buku sedang dipinjam</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19"/></svg></span></div><div class="stat-value">{{ number_format($activeLoans, 0, ',', '.') }}</div><div class="stat-foot">Salinan buku yang belum kembali</div></article>
        <article class="stat-card loan-stat-overdue"><div class="stat-top"><span class="stat-label">Melewati tenggat</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div><div class="stat-value">{{ number_format($overdueLoans, 0, ',', '.') }}</div><div class="stat-foot">Salinan perlu ditindaklanjuti</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Denda diterima</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M6 4h12v16H6zM9 11h6M9 15h4"/></svg></span></div><div class="stat-value stat-currency">Rp{{ number_format($collectedFines, 0, ',', '.') }}</div><div class="stat-foot">Pembayaran yang sudah dicatat</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Sisa denda</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5c0-1.2-1.2-2-3-2s-3 .8-3 2 1 1.8 3 2.4 3 1.2 3 2.4-1.2 2-3 2-3-.8-3-2"/></svg></span></div><div class="stat-value stat-currency">Rp{{ number_format($outstandingFines, 0, ',', '.') }}</div><div class="stat-foot">Denda belum dibayar lunas</div></article>
    </section>

    <section class="content-card">
        <div class="card-heading catalog-heading">
            <div><h2>Riwayat peminjaman</h2><p>{{ number_format($loans->total(), 0, ',', '.') }} judul tercatat</p></div>
            <form class="search-form" action="{{ route('loans.index') }}" method="GET" role="search">
                <label class="visually-hidden" for="loan-search">Cari transaksi</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                <input id="loan-search" type="search" name="search" value="{{ $search }}" placeholder="Kode, anggota, judul buku..." maxlength="100">
                <label class="visually-hidden" for="loan-status">Saring status</label>
                <select id="loan-status" name="status"><option value="">Semua status</option><option value="active" @selected($status === 'active')>Dipinjam</option><option value="overdue" @selected($status === 'overdue')>Terlambat</option><option value="returned" @selected($status === 'returned')>Dikembalikan</option></select>
                <button class="search-submit" type="submit">Cari</button>
            </form>
        </div>

        @if ($loans->isNotEmpty())
            <div class="table-wrap"><table class="data-table loan-table">
                <thead><tr><th>Kode transaksi</th><th>Anggota</th><th>Buku</th><th>Tenggat</th><th>Status</th><th>Denda</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($loans as $loan)
                        <tr>
                            <td><span class="book-code">{{ $loan->loan_code }}</span></td>
                            <td><strong class="loan-primary">{{ $loan->member->name }}</strong><small class="loan-secondary">{{ $loan->member->member_code }}</small></td>
                            <td><strong class="loan-primary">{{ $loan->book->title }}</strong><small class="loan-secondary">{{ $loan->book->book_code }}</small></td>
                            <td>{{ $loan->due_at->locale('id')->translatedFormat('d M Y') }}</td>
                            <td>
                                @if ($loan->returned_at)
                                    <span class="status-pill is-active">Dikembalikan</span>
                                @elseif ($loan->isOverdue())
                                    <span class="status-pill is-overdue">Terlambat</span>
                                @else
                                    <span class="status-pill is-pending">Dipinjam</span>
                                @endif
                            </td>
                            @php($remainingFine = max(0, $loan->fine_amount - (int) ($loan->paid_fines ?? 0)))
                            <td>{{ $loan->fine_amount > 0 ? ($remainingFine > 0 ? 'Rp'.number_format($remainingFine, 0, ',', '.') : 'Lunas') : '—' }}</td>
                            <td><a class="row-link" href="{{ route('loans.show', $loan) }}">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            @if ($loans->hasPages()) <div class="pagination-wrap">{{ $loans->links() }}</div> @endif
        @else
            <div class="empty-state"><span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19M9 8h6"/></svg></span><h3>{{ $search !== '' || $status !== '' ? 'Transaksi tidak ditemukan' : 'Belum ada peminjaman' }}</h3><p>{{ $search !== '' || $status !== '' ? 'Ubah kata kunci atau filter untuk menemukan transaksi.' : 'Peminjaman yang dicatat akan muncul di sini.' }}</p>@if ($search !== '' || $status !== '')<a class="button button-secondary" href="{{ route('loans.index') }}">Hapus filter</a>@else<a class="button" href="{{ route('loans.create') }}">Catat peminjaman</a>@endif</div>
        @endif
    </section>
@endsection
