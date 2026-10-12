<?php

namespace App\Models;

use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_code',
        'transaction_code',
        'member_id',
        'book_id',
        'processed_by',
        'borrowed_at',
        'due_at',
        'returned_at',
        'late_days',
        'fine_amount',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date',
            'due_at' => 'date',
            'returned_at' => 'datetime',
            'late_days' => 'integer',
            'fine_amount' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function finePayments(): HasMany
    {
        return $this->hasMany(FinePayment::class);
    }

    public function batch(): HasMany
    {
        return $this->hasMany(self::class, 'transaction_code', 'transaction_code')->orderBy('id');
    }

    public function isOverdue(): bool
    {
        return $this->returned_at === null && $this->due_at->lt(today());
    }
}
