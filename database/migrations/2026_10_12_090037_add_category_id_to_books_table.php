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
        if (Schema::hasColumn('books', 'category_id')) {
            return;
        }

        Schema::table('books', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('books', 'category_id')) {
            return;
        }

        Schema::table('books', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
