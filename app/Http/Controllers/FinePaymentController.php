<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinePaymentRequest;
use App\Models\FinePayment;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinePaymentController extends Controller
{
    public function store(StoreFinePaymentRequest $request, Loan $loan): RedirectResponse
    {
        $data = $request->validated();

        $payment = DB::transaction(function () use ($data, $loan, $request): FinePayment {
            $loan = Loan::query()->whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->returned_at === null || $loan->fine_amount < 1) {
                throw ValidationException::withMessages(['amount' => 'Pembayaran denda hanya dapat dicatat untuk pinjaman terlambat yang sudah dikembalikan.']);
            }

            $paidSoFar = (int) $loan->finePayments()->sum('amount');
            $remaining = $loan->fine_amount - $paidSoFar;

            if ($remaining < 1) {
                throw ValidationException::withMessages(['amount' => 'Denda transaksi ini sudah lunas.']);
            }

            if ($data['amount'] > $remaining) {
                throw ValidationException::withMessages(['amount' => 'Jumlah pembayaran melebihi sisa denda Rp'.number_format($remaining, 0, ',', '.').'.']);
            }

            return $loan->finePayments()->create([
                'received_by' => $request->user()->id,
                'amount' => $data['amount'],
                'paid_at' => now(),
                'note' => $data['note'] ?? null,
            ]);
        });

        return redirect()->route('loans.show', $loan)->with(
            'success',
            'Pembayaran Rp'.number_format($payment->amount, 0, ',', '.').' berhasil dicatat.'
        );
    }
}
