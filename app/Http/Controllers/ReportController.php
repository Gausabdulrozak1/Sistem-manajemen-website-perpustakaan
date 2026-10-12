<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\FinePayment;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $periodQuery = static fn (Builder $query): Builder => $query->whereDate('borrowed_at', '>=', $from)->whereDate('borrowed_at', '<=', $to);
        $today = today()->toDateString();

        $totalLoans = Loan::query()->whereDate('borrowed_at', '>=', $from)->whereDate('borrowed_at', '<=', $to)->distinct()->count('transaction_code');
        $activeLoans = Loan::query()->whereDate('borrowed_at', '>=', $from)->whereDate('borrowed_at', '<=', $to)->whereNull('returned_at')->count();
        $overdueLoans = Loan::query()->whereDate('borrowed_at', '>=', $from)->whereDate('borrowed_at', '<=', $to)->whereNull('returned_at')->whereDate('due_at', '<', $today)->count();
        $assessedFines = Loan::query()->whereNotNull('returned_at')->whereDate('returned_at', '>=', $from)->whereDate('returned_at', '<=', $to)->sum('fine_amount');
        $collectedFines = FinePayment::query()->whereDate('paid_at', '>=', $from)->whereDate('paid_at', '<=', $to)->sum('amount');
        $outstandingFines = max(0, (int) Loan::query()->whereNotNull('returned_at')->sum('fine_amount') - (int) FinePayment::query()->sum('amount'));

        $topBooks = Book::query()
            ->withCount(['loans as loans_in_period' => $periodQuery])
            ->whereHas('loans', $periodQuery)
            ->orderByDesc('loans_in_period')
            ->orderBy('title')
            ->limit(5)
            ->get();

        $topMembers = Member::query()
            ->withCount(['loans as loans_in_period' => $periodQuery])
            ->whereHas('loans', $periodQuery)
            ->orderByDesc('loans_in_period')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $recentLoans = Loan::query()
            ->with(['member:id,member_code,name', 'book:id,book_code,title'])
            ->whereDate('borrowed_at', '>=', $from)
            ->whereDate('borrowed_at', '<=', $to)
            ->latest('borrowed_at')
            ->latest('id')
            ->limit(8)
            ->get();

        $monthlyTrend = collect(range(5, 0))->map(function (int $monthsAgo): array {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $start = $month->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            return [
                'label' => $month->locale('id')->translatedFormat('M'),
                'count' => Loan::query()->whereDate('borrowed_at', '>=', $start)->whereDate('borrowed_at', '<=', $end)->distinct()->count('transaction_code'),
            ];
        });
        $maximumMonthlyCount = max(1, $monthlyTrend->max('count'));

        return view('reports.index', compact(
            'from', 'to', 'totalLoans', 'activeLoans', 'overdueLoans',
            'assessedFines', 'collectedFines', 'outstandingFines', 'topBooks', 'topMembers',
            'recentLoans', 'monthlyTrend', 'maximumMonthlyCount',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        return response()->streamDownload(function () use ($from, $to): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Kode transaksi', 'Kode item', 'Nomor anggota', 'Nama anggota', 'Kode buku', 'Judul buku',
                'Tanggal pinjam', 'Batas kembali', 'Tanggal kembali', 'Status',
                'Hari terlambat', 'Denda', 'Denda dibayar', 'Sisa denda',
            ], ';', '"', '\\');

            Loan::query()
                ->with(['member:id,member_code,name', 'book:id,book_code,title'])
                ->withSum('finePayments as paid_fines', 'amount')
                ->whereDate('borrowed_at', '>=', $from)
                ->whereDate('borrowed_at', '<=', $to)
                ->orderBy('borrowed_at')
                ->orderBy('id')
                ->chunk(500, function ($loans) use ($output): void {
                    foreach ($loans as $loan) {
                        $paid = (int) ($loan->paid_fines ?? 0);
                        $remaining = max(0, $loan->fine_amount - $paid);
                        $status = $loan->returned_at
                            ? 'Dikembalikan'
                            : ($loan->isOverdue() ? 'Terlambat' : 'Dipinjam');

                        fputcsv($output, array_map($this->csvSafe(...), [
                            $loan->transaction_code,
                            $loan->loan_code,
                            $loan->member->member_code,
                            $loan->member->name,
                            $loan->book->book_code,
                            $loan->book->title,
                            $loan->borrowed_at->toDateString(),
                            $loan->due_at->toDateString(),
                            $loan->returned_at?->format('Y-m-d H:i'),
                            $status,
                            (string) $loan->late_days,
                            (string) $loan->fine_amount,
                            (string) $paid,
                            (string) $remaining,
                        ]), ';', '"', '\\');
                    }
                });

            fclose($output);
        }, "laporan-peminjaman-{$from}-{$to}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{string, string} */
    private function period(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'from.date' => 'Tanggal awal tidak valid.',
            'to.date' => 'Tanggal akhir tidak valid.',
            'to.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
        ]);

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();

        return [$from, $to];
    }

    private function csvSafe(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }
}
