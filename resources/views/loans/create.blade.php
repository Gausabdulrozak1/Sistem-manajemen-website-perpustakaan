@extends('layouts.app')

@section('title', 'Catat peminjaman')
@section('breadcrumb', 'Peminjaman baru')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Sirkulasi buku</p><h1>Peminjaman baru</h1><p>Pilih anggota aktif dan buku yang stoknya tersedia.</p></div>
        <a class="button button-secondary" href="{{ route('loans.index') }}">Kembali ke sirkulasi</a>
    </div>

    @if ($errors->any())
        <div class="notice notice-error" role="alert"><strong>Peminjaman belum dapat dicatat.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form class="content-card form-card loan-form" method="POST" action="{{ route('loans.store') }}">
        @csrf
        <h2 class="form-section-title">Informasi peminjaman</h2>
        <div class="form-grid">
            <div class="field field-full"><label for="member_id">Anggota <span>*</span></label>
                <select id="member_id" name="member_id" required>
                    <option value="">Pilih anggota aktif</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->member_code }} · {{ $member->name }} · {{ $member->active_loans_count }}/{{ $maximumActiveLoans }} buku</option>
                    @endforeach
                </select>
                @if ($members->isEmpty())<p class="field-hint">Belum ada anggota aktif yang memenuhi batas pinjaman. Periksa status anggota atau peminjaman yang belum kembali.</p>@endif
                @error('member_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field field-full"><fieldset class="loan-book-fieldset" aria-describedby="books-hint">
                <legend>Buku <span>*</span></legend>
                <p id="books-hint" class="field-hint">Pilih satu atau beberapa judul. Maksimal 10 buku dalam satu transaksi.</p>
                @if ($books->isNotEmpty())
                    <div class="loan-book-picker">
                        @foreach ($books as $book)
                            <label class="loan-book-option"><input type="checkbox" name="book_ids[]" value="{{ $book->id }}" @checked(in_array((string) $book->id, is_array(old('book_ids')) ? array_map('strval', old('book_ids')) : [], true))><span class="loan-book-check" aria-hidden="true"></span><span class="loan-book-option-copy"><strong>{{ $book->title }}</strong><small>{{ $book->book_code }} · {{ $book->shelf_location ?: 'Lokasi rak belum diatur' }}</small></span><span class="loan-book-stock">{{ $book->available_stock }} tersedia</span></label>
                        @endforeach
                    </div>
                @else
                    <p class="field-hint">Belum ada buku yang tersedia untuk dipinjam. Tambahkan judul atau proses pengembalian lebih dahulu.</p>
                @endif
                @error('book_ids') <p class="field-error">{{ $message }}</p> @enderror
            </fieldset></div>
        </div>

        <aside class="loan-policy" aria-label="Ketentuan peminjaman">
            <span class="loan-policy-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg></span>
            <div><strong>Ketentuan pinjam</strong><p>Batas pinjaman {{ $maximumActiveLoans }} buku per anggota. Buku kembali dalam {{ $loanPeriodDays }} hari. Denda keterlambatan Rp{{ number_format(config('library.daily_fine_rupiah'), 0, ',', '.') }} per hari.</p></div>
        </aside>

        <div class="form-actions"><a class="button button-secondary" href="{{ route('loans.index') }}">Batal</a><button class="button" type="submit" @disabled($members->isEmpty() || $books->isEmpty())>Simpan peminjaman</button></div>
    </form>
@endsection
