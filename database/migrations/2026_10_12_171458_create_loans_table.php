<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->string('loan_code', 24)->unique();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('borrowed_at');
            $table->date('due_at')->index();
            $table->dateTime('returned_at')->nullable()->index();
            $table->unsignedSmallInteger('late_days')->default(0);
            $table->unsignedInteger('fine_amount')->default(0);
            $table->timestamps();

            $table->index(['member_id', 'returned_at']);
            $table->index(['book_id', 'returned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
