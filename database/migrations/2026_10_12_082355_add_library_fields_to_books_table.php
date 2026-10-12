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
        $catalogColumns = [
            'book_code',
            'title',
            'author',
            'publisher',
            'publication_year',
            'total_stock',
            'available_stock',
            'shelf_location',
        ];

        $missingColumns = array_values(array_filter(
            $catalogColumns,
            static fn (string $column): bool => ! Schema::hasColumn('books', $column),
        ));

        if ($missingColumns === []) {
            return;
        }

        Schema::table('books', function (Blueprint $table) use ($missingColumns): void {
            if (in_array('book_code', $missingColumns, true)) {
                $table->string('book_code', 30)->unique();
            }

            if (in_array('title', $missingColumns, true)) {
                $table->string('title');
            }

            if (in_array('author', $missingColumns, true)) {
                $table->string('author');
            }

            if (in_array('publisher', $missingColumns, true)) {
                $table->string('publisher');
            }

            if (in_array('publication_year', $missingColumns, true)) {
                $table->unsignedSmallInteger('publication_year')->nullable();
            }

            if (in_array('total_stock', $missingColumns, true)) {
                $table->unsignedInteger('total_stock')->default(0);
            }

            if (in_array('available_stock', $missingColumns, true)) {
                $table->unsignedInteger('available_stock')->default(0);
            }

            if (in_array('shelf_location', $missingColumns, true)) {
                $table->string('shelf_location')->nullable();
            }
        });
    }

    /**
     * Remove the field added beyond the original books-table schema.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('books', 'book_code')) {
            return;
        }

        Schema::table('books', function (Blueprint $table): void {
            $table->dropUnique(['book_code']);
            $table->dropColumn('book_code');
        });
    }
};
