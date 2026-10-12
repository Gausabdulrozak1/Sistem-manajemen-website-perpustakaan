<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->string('transaction_code', 24)->nullable()->after('loan_code');
        });

        DB::table('loans')->whereNull('transaction_code')->update([
            'transaction_code' => DB::raw('loan_code'),
        ]);

        Schema::table('loans', function (Blueprint $table): void {
            $table->index('transaction_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['transaction_code']);
            $table->dropColumn('transaction_code');
        });
    }
};
