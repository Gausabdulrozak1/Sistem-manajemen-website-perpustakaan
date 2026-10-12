@extends('layouts.app')

@section('title', 'Laporan')
@section('breadcrumb', 'Laporan')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Analisis perpustakaan</p><h1>Laporan</h1><p>Pantau aktivitas sirkulasi dan unduh rekap transaksi.</p></div>
        <a class="button" href="{{ route('reports.export', ['from' => $from, 'to' => $to]) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 17v3h16v-3"/></svg>Unduh CSV</a>
    </div>

    @if ($errors->any()) <div class="notice notice-error" role="alert">{{ $errors->first() }}</div> @endif

    <section class="content-card report-filter-card">
        <form class="report-filter" method="GET" action="{{ route('reports.index') }}">
            <div class="field"><label for="from">Dari tanggal</label><input id="from" type="date" name="from" value="{{ $from }}" required></div>
            <div class="field"><label for="to">Sampai tanggal</label><input id="to" type="date" name="to" value="{{ $to }}" required></div>
            <button class="button" type="submit">Terapkan periode</button>
            <span class="report-period-note">Periode laporan: {{ \Illuminate\Support\Carbon::parse($from)->locale('id')->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->locale('id')->translatedFormat('d M Y') }}</span>
        </form>
    </section>

    <section class="stats-grid report-stats" aria-label="Ringkasan laporan">
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Peminjaman tercatat</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4.5h11.5A2.5 2.5 0 0 1 19 7v13H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5A2.5 2.5 0 0 1 7.5 15H19"/></svg></span></div><div class="stat-value">{{ number_format($totalLoans, 0, ',', '.') }}</div><div class="stat-foot">Dipinjam dalam periode ini</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Buku masih dipinjam</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div><div class="stat-value">{{ number_format($activeLoans, 0, ',', '.') }}</div><div class="stat-foot">Dari transaksi pada periode ini</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Terlambat</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2.8 20h18.4L12 3Z"/><path d="M12 9v5M12 17h.01"/></svg></span></div><div class="stat-value">{{ number_format($overdueLoans, 0, ',', '.') }}</div><div class="stat-foot">Perlu ditindaklanjuti</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Denda dikenakan</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M6 4h12v16H6zM9 11h6M9 15h4"/></svg></span></div><div class="stat-value stat-currency">Rp{{ number_format($assessedFines, 0, ',', '.') }}</div><div class="stat-foot">Buku yang kembali dalam periode</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Pembayaran denda</span><span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="9"/></svg></span></div><div class="stat-value stat-currency">Rp{{ number_format($collectedFines, 0, ',', '.') }}</div><div class="stat-foot">Diterima dalam periode</div></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-label">Sisa denda</span><span class="stat-icon gold"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M15 9.5c0-1.2-1.2-2-3-2s-3 .8-3 2 1 1.8 3 2.4 3 1.2 3 2.4-1.2 2-3 2-3-.8-3-2"/></svg></span></div><div class="stat-value stat-currency">Rp{{ number_format($outstandingFines, 0, ',', '.') }}</div><div class="stat-foot">Belum lunas dari semua transaksi</div></article>
    </section>

    <section class="report-grid">
        <article class="content-card report-chart-card">
            <div class="card-heading"><div><h2>Tren peminjaman</h2><p>Jumlah transaksi per bulan selama enam bulan terakhir.</p></div></div>
            <div class="trend-chart" role="img" aria-label="Grafik jumlah peminjaman selama enam bulan terakhir">
                @foreach ($monthlyTrend as $month)
                    <div class="trend-column"><span class="trend-count">{{ $month['count'] }}</span><div class="trend-track"><div class="trend-bar" style="height: {{ max(6, ($month['count'] / $maximumMonthlyCount) * 100) }}%"></div></div><span class="trend-month">{{ $month['label'] }}</span></div>
                @endforeach
            </div>
        </article>
        <article class="content-card report-ranking-card">
            <div class="card-heading"><div><h2>Buku paling sering dipinjam</h2><p>Peringkat berdasarkan periode yang dipilih.</p></div></div>
            @if ($topBooks->isNotEmpty())
                <ol class="ranking-list">@foreach ($topBooks as $book)<li><span class="ranking-number">{{ $loop->iteration }}</span><span class="ranking-copy"><strong>{{ $book->title }}</strong><small>{{ $book->book_code }} · {{ $book->author }}</small></span><b>{{ number_format($book->loans_in_period, 0, ',', '.') }}×</b></li>@endforeach</ol>
            @else
                <div class="report-empty">Belum ada peminjaman pada periode ini.</div>
            @endif
        </article>
    </section>

    <section class="report-grid report-grid-lower">
        <article class="content-card report-ranking-card">
            <div class="card-heading"><div><h2>Anggota paling aktif</h2><p>Anggota dengan transaksi terbanyak pada periode ini.</p></div></div>
            @if ($topMembers->isNotEmpty())
                <ol class="ranking-list">@foreach ($topMembers as $member)<li><span class="ranking-number">{{ $loop->iteration }}</span><span class="ranking-copy"><strong>{{ $member->name }}</strong><small>{{ $member->member_code }}</small></span><b>{{ number_format($member->loans_in_period, 0, ',', '.') }}×</b></li>@endforeach</ol>
            @else
                <div class="report-empty">Belum ada aktivitas anggota pada periode ini.</div>
            @endif
        </article>
        <article class="content-card report-ranking-card">
            <div class="card-heading"><div><h2>Peminjaman terbaru</h2><p>Transaksi pada rentang tanggal yang dipilih.</p></div><a class="text-link" href="{{ route('loans.index') }}">Semua transaksi&nbsp;→</a></div>
            @if ($recentLoans->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Transaksi</th><th>Anggota</th><th>Buku</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
                    @foreach ($recentLoans as $loan)
                        <tr><td><a class="row-link" href="{{ route('loans.show', $loan) }}">{{ $loan->loan_code }}</a></td><td>{{ $loan->member->name }}</td><td class="book-title">{{ $loan->book->title }}</td><td>{{ $loan->borrowed_at->locale('id')->translatedFormat('d M Y') }}</td><td>@if ($loan->returned_at)<span class="status-pill is-active">Kembali</span>@elseif ($loan->isOverdue())<span class="status-pill is-overdue">Terlambat</span>@else<span class="status-pill is-pending">Dipinjam</span>@endif</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <div class="report-empty">Transaksi pada periode ini akan muncul di sini.</div>
            @endif
        </article>
    </section>
@endsection
