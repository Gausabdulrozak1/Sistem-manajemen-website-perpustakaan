@extends('layouts.app')

@section('title', 'Detail '.$loan->loan_code)
@section('breadcrumb', 'Detail peminjaman')

@section('content')
    @php
        $paidFine = (int) $loan->finePayments->sum('amount');
        $remainingFine = max(0, $loan->fine_amount - $paidFine);
    @endphp
    @if (session('success')) <div class="notice" role="status">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="notice notice-error" role="alert">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="notice notice-error" role="alert">{{ $errors->first() }}</div> @endif

    <div class="page-heading detail-heading">
        <div><p class="eyebrow">Transaksi sirkulasi · {{ $loan->loan_code }}</p><h1>Detail peminjaman</h1><p>Catatan peminjaman buku dan status pengembaliannya.</p></div>
        <div class="heading-actions">
            <a class="button button-secondary" href="{{ route('loans.index') }}">Kembali ke sirkulasi</a>
            @if (! $loan->returned_at)
                <form method="POST" action="{{ route('loans.return', $loan) }}">@csrf<button class="button" type="submit">Catat pengembalian</button></form>
            @endif
        </div>
    </div>

    <section class="content-card detail-card loan-detail-card">
        <div class="detail-banner">
            <span class="detail-book-icon" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M6 8.5c3.8-.9 7.2-.2 10 2.1v14c-2.8-2.3-6.2-3-10-2.1v-14Zm20 0c-3.8-.9-7.2-.2-10 2.1v14c2.8-2.3 6.2-3 10-2.1v-14Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M16 10.6v14" stroke="currentColor" stroke-width="1.6"/></svg></span>
            <div><span class="book-code">{{ $loan->loan_code }}</span><p>Kode transaksi {{ $loan->transaction_code }}</p><p>
                @if ($loan->returned_at)<span class="status-pill is-active">Dikembalikan</span>@elseif ($loan->isOverdue())<span class="status-pill is-overdue">Terlambat</span>@else<span class="status-pill is-pending">Sedang dipinjam</span>@endif
            </p></div>
        </div>
        <div class="detail-grid loan-detail-grid">
            <div class="detail-item"><span>Anggota</span><strong>{{ $loan->member->name }}</strong><small>{{ $loan->member->member_code }}</small></div>
            <div class="detail-item"><span>Buku</span><strong>{{ $loan->book->title }}</strong><small>{{ $loan->book->book_code }}</small></div>
            <div class="detail-item"><span>Lokasi rak</span><strong>{{ $loan->book->shelf_location ?: 'Belum ditentukan' }}</strong></div>
            <div class="detail-item"><span>Tanggal pinjam</span><strong>{{ $loan->borrowed_at->locale('id')->translatedFormat('d F Y') }}</strong></div>
            <div class="detail-item"><span>Batas pengembalian</span><strong>{{ $loan->due_at->locale('id')->translatedFormat('d F Y') }}</strong></div>
            <div class="detail-item"><span>Dikembalikan</span><strong>{{ $loan->returned_at?->locale('id')->translatedFormat('d F Y, H:i') ?? 'Belum dikembalikan' }}</strong></div>
            @if ($loan->returned_at)
                <div class="detail-item"><span>Hari terlambat</span><strong>{{ number_format($loan->late_days, 0, ',', '.') }} hari</strong></div>
                <div class="detail-item"><span>Denda</span><strong class="{{ $loan->fine_amount > 0 ? 'fine-amount' : 'detail-available' }}">Rp{{ number_format($loan->fine_amount, 0, ',', '.') }}</strong></div>
                <div class="detail-item"><span>Sudah dibayar</span><strong>Rp{{ number_format($paidFine, 0, ',', '.') }}</strong></div>
                <div class="detail-item"><span>Sisa denda</span><strong class="{{ $remainingFine > 0 ? 'fine-amount' : 'detail-available' }}">Rp{{ number_format($remainingFine, 0, ',', '.') }}</strong></div>
            @endif
            <div class="detail-item"><span>Diproses oleh</span><strong>{{ $loan->processedBy?->name ?? 'Admin perpustakaan' }}</strong></div>
        </div>
        @if ($loan->batch->count() > 1)
            <div class="loan-batch-list">
                <h2>Buku dalam transaksi ini <span>{{ $loan->batch->count() }} judul</span></h2>
                @foreach ($loan->batch as $batchLoan)
                    <div class="loan-batch-item"><span class="loan-batch-status {{ $batchLoan->returned_at ? 'is-returned' : ($batchLoan->isOverdue() ? 'is-late' : '') }}"></span><span><strong>{{ $batchLoan->book->title }}</strong><small>{{ $batchLoan->book->book_code }} · {{ $batchLoan->loan_code }}</small></span><span class="loan-batch-label">{{ $batchLoan->returned_at ? 'Kembali' : ($batchLoan->isOverdue() ? 'Terlambat' : 'Dipinjam') }}</span><a class="row-link" href="{{ route('loans.show', $batchLoan) }}">Detail</a></div>
                @endforeach
            </div>
        @endif
        @if (! $loan->returned_at)
            <div class="loan-open-note"><span class="status-dot"></span>Buku ini masih mengurangi stok tersedia. Stok bertambah kembali setelah pengembalian dicatat.</div>
        @endif
    </section>
    @if ($loan->returned_at && $loan->fine_amount > 0)
        <section class="content-card fine-payment-card">
            <div class="card-heading"><div><h2>Pembayaran denda</h2><p>Riwayat pembayaran untuk transaksi ini.</p></div><span class="status-pill {{ $remainingFine === 0 ? 'is-active' : 'is-pending' }}">{{ $remainingFine === 0 ? 'Lunas' : ($paidFine > 0 ? 'Dibayar sebagian' : 'Belum lunas') }}</span></div>
            @if ($loan->finePayments->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Tanggal pembayaran</th><th>Jumlah</th><th>Diterima oleh</th><th>Catatan</th></tr></thead><tbody>
                    @foreach ($loan->finePayments as $payment)
                        <tr><td>{{ $payment->paid_at->locale('id')->translatedFormat('d M Y, H:i') }}</td><td class="book-title">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td><td>{{ $payment->receivedBy?->name ?? 'Admin perpustakaan' }}</td><td>{{ $payment->note ?: '—' }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
            @if ($remainingFine > 0)
                <form class="fine-payment-form" method="POST" action="{{ route('fine-payments.store', $loan) }}">
                    @csrf
                    <div class="fine-payment-intro"><strong>Catat pembayaran baru</strong><span>Sisa denda Rp{{ number_format($remainingFine, 0, ',', '.') }}</span></div>
                    <div class="fine-payment-fields">
                        <div class="field"><label for="amount">Jumlah dibayar <span>*</span></label><input id="amount" type="number" name="amount" min="1" max="{{ $remainingFine }}" step="1" value="{{ old('amount', $remainingFine) }}" required>@error('amount')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="field"><label for="note">Catatan <span>(opsional)</span></label><input id="note" type="text" name="note" maxlength="255" value="{{ old('note') }}" placeholder="Contoh: pembayaran tunai">@error('note')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <button class="button" type="submit">Simpan pembayaran</button>
                    </div>
                </form>
            @endif
        </section>
    @endif
@endsection
