@extends('layouts.app')

@section('title', 'Anggota')
@section('breadcrumb', 'Anggota')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Data perpustakaan</p><h1>Anggota</h1><p>Kelola informasi dan status keanggotaan pembaca.</p></div>
        <a class="button" href="{{ route('members.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah anggota</a>
    </div>
    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    <section class="content-card">
        <div class="card-heading catalog-heading">
            <div><h2>Daftar anggota</h2><p>{{ number_format($members->total(), 0, ',', '.') }} anggota terdaftar</p></div>
            <form class="search-form" action="{{ route('members.index') }}" method="GET" role="search">
                <label class="visually-hidden" for="member-search">Cari anggota</label>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                <input id="member-search" type="search" name="search" value="{{ $search }}" placeholder="Cari nama, nomor, email..." maxlength="100">
                <label class="visually-hidden" for="member-status-filter">Saring status</label>
                <select id="member-status-filter" name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Tidak aktif</option>
                </select>
                <button class="search-submit" type="submit">Cari</button>
            </form>
        </div>
        @if ($members->isNotEmpty())
            <div class="table-wrap"><table class="data-table">
                <thead><tr><th>Nomor anggota</th><th>Nama</th><th>Email / telepon</th><th>Bergabung</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>@foreach ($members as $member)
                    <tr>
                        <td><span class="book-code">{{ $member->member_code }}</span></td>
                        <td class="book-title">{{ $member->name }}</td>
                        <td>{{ $member->email ?: $member->phone ?: '—' }}</td>
                        <td>{{ $member->joined_at->locale('id')->translatedFormat('d M Y') }}</td>
                        <td><span class="status-pill {{ $member->status === 'active' ? 'is-active' : 'is-inactive' }}">{{ $member->status === 'active' ? 'Aktif' : 'Tidak aktif' }}</span></td>
                        <td><div class="row-actions">
                            <a class="row-link" href="{{ route('members.show', $member) }}">Lihat</a>
                            <a class="row-link" href="{{ route('members.edit', $member) }}">Edit</a>
                            <form action="{{ route('members.destroy', $member) }}" method="POST" data-confirm-delete="Hapus anggota {{ $member->member_code }} dari daftar?">
                                @csrf @method('DELETE')
                                <button class="row-link row-link-danger" type="submit">Hapus</button>
                            </form>
                        </div></td>
                    </tr>
                @endforeach</tbody>
            </table></div>
            @if ($members->hasPages()) <div class="pagination-wrap">{{ $members->links() }}</div> @endif
        @else
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M16 5.2a3.5 3.5 0 0 1 0 6.6M17 14a5.6 5.6 0 0 1 4.2 5.4"/></svg></span>
                <h3>{{ $search !== '' || $status !== '' ? 'Anggota tidak ditemukan' : 'Belum ada anggota' }}</h3>
                <p>{{ $search !== '' || $status !== '' ? 'Ubah kata kunci atau filter status untuk melihat hasil lain.' : 'Daftarkan anggota pertama agar peminjaman dapat dicatat.' }}</p>
                @if ($search !== '' || $status !== '')
                    <a class="button button-secondary" href="{{ route('members.index') }}">Hapus filter</a>
                @else
                    <a class="button" href="{{ route('members.create') }}">Tambah anggota</a>
                @endif
            </div>
        @endif
    </section>
@endsection
