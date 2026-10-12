<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Models\Book;
use App\Models\FinePayment;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'overdue', 'returned'])],
        ], [
            'search.max' => 'Kata kunci pencarian maksimal :max karakter.',
            'status.in' => 'Status peminjaman tidak valid.',
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? '';
        $today = today()->toDateString();

        $loans = Loan::query()
            ->with(['member:id,member_code,name', 'book:id,book_code,title'])
            ->withSum('finePayments as paid_fines', 'amount')
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $pattern = '%'.$search.'%';

                $query->where(static function (Builder $query) use ($pattern): void {
                    $query->where('loan_code', 'like', $pattern)
                        ->orWhereHas('member', static function (Builder $query) use ($pattern): void {
                            $query->where('member_code', 'like', $pattern)
                                ->orWhere('name', 'like', $pattern);
                        })
                        ->orWhereHas('book', static function (Builder $query) use ($pattern): void {
                            $query->where('book_code', 'like', $pattern)
                                ->orWhere('title', 'like', $pattern);
                        });
                });
            })
            ->when($status === 'active', static fn (Builder $query) => $query->whereNull('returned_at'))
            ->when($status === 'overdue', static fn (Builder $query) => $query->whereNull('returned_at')->whereDate('due_at', '<', $today))
            ->when($status === 'returned', static fn (Builder $query) => $query->whereNotNull('returned_at'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $activeLoans = Loan::query()->whereNull('returned_at')->count();
        $overdueLoans = Loan::query()->whereNull('returned_at')->whereDate('due_at', '<', $today)->count();
        $collectedFines = FinePayment::query()->sum('amount');
        $assessedFines = Loan::query()->whereNotNull('returned_at')->sum('fine_amount');
        $outstandingFines = max(0, $assessedFines - $collectedFines);

        return view('loans.index', compact('loans', 'search', 'status', 'activeLoans', 'overdueLoans', 'collectedFines', 'outstandingFines'));
    }

    public function create(): View
    {
        $maximumActiveLoans = (int) config('library.maximum_active_loans_per_member');
        $members = Member::query()
            ->where('status', 'active')
            ->withCount(['loans as active_loans_count' => static fn (Builder $query) => $query->whereNull('returned_at')])
            ->whereRaw('(select count(*) from loans where loans.member_id = members.id and loans.returned_at is null) < ?', [$maximumActiveLoans])
            ->orderBy('name')
            ->get(['id', 'member_code', 'name']);
        $books = Book::query()->where('available_stock', '>', 0)->orderBy('title')->get([
            'id', 'book_code', 'title', 'available_stock',
        ]);

        return view('loans.create', [
            'members' => $members,
            'books' => $books,
            'loanPeriodDays' => (int) config('library.loan_period_days'),
            'maximumActiveLoans' => $maximumActiveLoans,
        ]);
    }

    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $bookIds = array_values(array_unique(array_map('intval', $data['book_ids'])));
        sort($bookIds);
        $loan = DB::transaction(function () use ($data, $bookIds, $request): Loan {
            $member = Member::query()->whereKey($data['member_id'])->lockForUpdate()->firstOrFail();

            if ($member->status !== 'active') {
                throw ValidationException::withMessages(['member_id' => 'Anggota tidak aktif dan belum dapat meminjam buku.']);
            }

            $maximumActiveLoans = (int) config('library.maximum_active_loans_per_member');
            $activeLoanCount = Loan::query()
                ->where('member_id', $member->id)
                ->whereNull('returned_at')
                ->count();

            if ($activeLoanCount + count($bookIds) > $maximumActiveLoans) {
                throw ValidationException::withMessages(['member_id' => "Anggota sudah mencapai batas {$maximumActiveLoans} buku yang sedang dipinjam."]);
            }

            $books = Book::query()->whereKey($bookIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($books->count() !== count($bookIds)) {
                throw ValidationException::withMessages(['book_ids' => 'Salah satu buku yang dipilih tidak lagi tersedia.']);
            }

            foreach ($books as $book) {
                if ($book->available_stock < 1) {
                    throw ValidationException::withMessages(['book_ids' => "Stok buku {$book->title} sudah habis. Pilih judul lain."]);
                }
            }

            $transactionCode = $this->newTransactionCode();
            $firstLoan = null;
            $borrowedAt = today();
            $dueAt = $borrowedAt->copy()->addDays((int) config('library.loan_period_days'));

            foreach ($books as $book) {
                $currentLoan = Loan::query()->create([
                    'loan_code' => $this->newLoanCode(),
                    'transaction_code' => $transactionCode,
                    'member_id' => $member->id,
                    'book_id' => $book->id,
                    'processed_by' => $request->user()->id,
                    'borrowed_at' => $borrowedAt,
                    'due_at' => $dueAt,
                ]);

                $book->decrement('available_stock');
                $firstLoan ??= $currentLoan;
            }

            return $firstLoan;
        });

        return redirect()->route('loans.show', $loan)->with('success', count($bookIds).' buku berhasil dicatat dalam transaksi '.$loan->transaction_code.'. Stok tersedia sudah diperbarui.');
    }

    public function show(Loan $loan): View
    {
        $loan->load(['member', 'book.category', 'processedBy', 'finePayments.receivedBy', 'batch.book']);

        return view('loans.show', compact('loan'));
    }

    public function returnBook(Loan $loan): RedirectResponse
    {
        $result = DB::transaction(function () use ($loan): array {
            $loan = Loan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->returned_at !== null) {
                return ['returned' => false, 'fine' => $loan->fine_amount];
            }

            $book = Book::query()->whereKey($loan->book_id)->lockForUpdate()->firstOrFail();
            $today = today();
            $lateDays = $loan->due_at->lt($today)
                ? (int) $loan->due_at->diffInDays($today)
                : 0;
            $fine = $lateDays * (int) config('library.daily_fine_rupiah');

            if ($book->available_stock >= $book->total_stock) {
                throw ValidationException::withMessages(['loan' => 'Stok buku perlu diperiksa sebelum pengembalian ini diselesaikan.']);
            }

            $book->increment('available_stock');
            $loan->update([
                'returned_at' => now(),
                'late_days' => $lateDays,
                'fine_amount' => $fine,
            ]);

            return ['returned' => true, 'fine' => $fine];
        });

        if (! $result['returned']) {
            return redirect()->route('loans.show', $loan)->with('error', 'Buku pada transaksi ini sudah dikembalikan.');
        }

        $message = $result['fine'] > 0
            ? 'Pengembalian dicatat. Denda keterlambatan Rp'.number_format($result['fine'], 0, ',', '.').'.'
            : 'Pengembalian dicatat tanpa denda.';

        return redirect()->route('loans.show', $loan)->with('success', $message);
    }

    private function newLoanCode(): string
    {
        do {
            $code = 'PJ-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Loan::query()->where('loan_code', $code)->exists());

        return $code;
    }

    private function newTransactionCode(): string
    {
        do {
            $code = 'TR-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Loan::query()->where('transaction_code', $code)->exists());

        return $code;
    }
}
