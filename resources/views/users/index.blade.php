@extends('layouts.app')

@section('title', 'Pengguna')
@section('breadcrumb', 'Pengguna')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Administrasi akses</p><h1>Pengguna</h1><p>Atur akun admin dan petugas yang dapat membuka sistem.</p></div>
        <a class="button" href="{{ route('users.create') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Tambah pengguna</a>
    </div>
    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="notice notice-error" role="alert">{{ $errors->first() }}</div> @endif

    <section class="content-card">
        <div class="card-heading"><div><h2>Daftar akun</h2><p>{{ number_format($users->total(), 0, ',', '.') }} akun terdaftar</p></div></div>
        @if ($users->isNotEmpty())
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
                @foreach ($users as $user)
                    <tr><td class="book-title">{{ $user->name }} @if (auth()->user()->is($user))<small class="current-user-label">Akun Anda</small>@endif</td><td>{{ $user->email }}</td><td><span class="status-pill {{ $user->isAdmin() ? 'is-active' : 'is-pending' }}">{{ $user->isAdmin() ? 'Admin' : 'Petugas' }}</span></td><td>{{ $user->created_at->locale('id')->translatedFormat('d M Y') }}</td><td><div class="row-actions"><a class="row-link" href="{{ route('users.edit', $user) }}">Edit</a>@if (! auth()->user()->is($user))<form action="{{ route('users.destroy', $user) }}" method="POST" data-confirm-delete="Hapus akses {{ $user->name }}? Akun ini tidak dapat masuk setelah dihapus.">@csrf @method('DELETE')<button class="row-link row-link-danger" type="submit">Hapus</button></form>@endif</div></td></tr>
                @endforeach
            </tbody></table></div>
            @if ($users->hasPages()) <div class="pagination-wrap">{{ $users->links() }}</div> @endif
        @else
            <div class="empty-state"><span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M17 8v6m-3-3h6"/></svg></span><h3>Belum ada pengguna</h3><p>Akun petugas dan admin akan tercatat di bagian ini.</p><a class="button" href="{{ route('users.create') }}">Tambah pengguna</a></div>
        @endif
    </section>
@endsection
