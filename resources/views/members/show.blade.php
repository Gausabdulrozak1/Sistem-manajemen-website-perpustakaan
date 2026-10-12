@extends('layouts.app')

@section('title', $member->name)
@section('breadcrumb', 'Detail Anggota')

@section('content')
    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="notice notice-error" role="alert">{{ session('error') }}</div> @endif
    <div class="page-heading detail-heading">
        <div><p class="eyebrow">Anggota perpustakaan · {{ $member->member_code }}</p><h1>{{ $member->name }}</h1><p>Terdaftar sejak {{ $member->joined_at->locale('id')->translatedFormat('d F Y') }}</p></div>
        <div class="heading-actions">
            <a class="button button-secondary" href="{{ route('members.index') }}">Kembali ke anggota</a>
            <a class="button" href="{{ route('members.edit', $member) }}">Edit anggota</a>
        </div>
    </div>
    <section class="content-card detail-card">
        <div class="detail-banner">
            <span class="member-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span>
            <div><span class="status-pill {{ $member->status === 'active' ? 'is-active' : 'is-inactive' }}">{{ $member->status === 'active' ? 'Aktif' : 'Tidak aktif' }}</span><p>Nomor anggota {{ $member->member_code }}</p></div>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><span>Email</span><strong>{{ $member->email ?: 'Belum dicatat' }}</strong></div>
            <div class="detail-item"><span>Nomor telepon</span><strong>{{ $member->phone ?: 'Belum dicatat' }}</strong></div>
            <div class="detail-item detail-wide"><span>Alamat</span><strong>{{ $member->address ?: 'Belum dicatat' }}</strong></div>
            <div class="detail-item"><span>Status anggota</span><strong>{{ $member->status === 'active' ? 'Aktif' : 'Tidak aktif' }}</strong></div>
            <div class="detail-item"><span>Tanggal bergabung</span><strong>{{ $member->joined_at->locale('id')->translatedFormat('d F Y') }}</strong></div>
        </div>
    </section>
    <section class="content-card member-loan-history">
        <div class="card-heading"><div><h2>Riwayat peminjaman</h2><p>Sepuluh transaksi terakhir anggota ini.</p></div></div>
        @if ($loanHistory->isNotEmpty())
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Kode transaksi</th><th>Buku</th><th>Tanggal pinjam</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                @foreach ($loanHistory as $loan)
                    <tr><td><span class="book-code">{{ $loan->loan_code }}</span></td><td class="book-title">{{ $loan->book->title }}</td><td>{{ $loan->borrowed_at->locale('id')->translatedFormat('d M Y') }}</td><td>{{ $loan->due_at->locale('id')->translatedFormat('d M Y') }}</td><td>@if ($loan->returned_at)<span class="status-pill is-active">Dikembalikan</span>@elseif ($loan->isOverdue())<span class="status-pill is-overdue">Terlambat</span>@else<span class="status-pill is-pending">Dipinjam</span>@endif</td><td><a class="row-link" href="{{ route('loans.show', $loan) }}">Detail</a></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="member-loan-footer"><a class="text-link" href="{{ route('loans.index', ['search' => $member->member_code]) }}">Lihat semua peminjaman anggota&nbsp;→</a></div>
        @else
            <div class="empty-state loan-history-empty"><span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19"/></svg></span><h3>Belum ada peminjaman</h3><p>Riwayat transaksi anggota akan tampil di bagian ini.</p></div>
        @endif
    </section>
@endsection
