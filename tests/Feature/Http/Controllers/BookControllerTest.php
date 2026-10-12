<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_returns_matching_books_and_excludes_unmatched_books(): void
    {
        $this->signIn();
        $matchingBook = Book::factory()->create([
            'book_code' => 'BK-SEARCH-01',
            'title' => 'Pengantar Sains',
        ]);
        $unmatchedBook = Book::factory()->create([
            'book_code' => 'BK-OTHER-01',
            'title' => 'Kumpulan Cerita',
        ]);

        $response = $this->get(route('books.index', ['search' => 'BK-SEARCH-01']));

        $response->assertOk()
            ->assertSee($matchingBook->title)
            ->assertDontSee($unmatchedBook->title);
    }

    public function test_search_treats_sql_syntax_as_plain_text(): void
    {
        $this->signIn();
        $book = Book::factory()->create(['title' => 'Judul Rahasia']);

        $response = $this->get(route('books.index', ['search' => "' OR 1=1 --"]));

        $response->assertOk()
            ->assertDontSee($book->title);
    }

    public function test_category_filter_returns_only_books_in_the_selected_category(): void
    {
        $this->signIn();
        $selectedCategory = Category::factory()->create(['name' => 'Sains']);
        $otherCategory = Category::factory()->create(['name' => 'Sastra']);
        $matchingBook = Book::factory()->create([
            'category_id' => $selectedCategory->id,
            'title' => 'Fisika Dasar',
        ]);
        $unmatchedBook = Book::factory()->create([
            'category_id' => $otherCategory->id,
            'title' => 'Cerita Pendek',
        ]);

        $response = $this->get(route('books.index', ['category_id' => $selectedCategory->id]));

        $response->assertOk()
            ->assertSee($matchingBook->title)
            ->assertDontSee($unmatchedBook->title);
    }

    public function test_show_escapes_user_provided_book_title(): void
    {
        $this->signIn();
        $dangerousTitle = '<script>alert("uji")</script>';
        $book = Book::factory()->create(['title' => $dangerousTitle]);

        $response = $this->get(route('books.show', $book));

        $response->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee($dangerousTitle, false);
    }

    public function test_edit_route_displays_the_current_book_data(): void
    {
        $this->signIn();
        $book = Book::factory()->create(['title' => 'Belajar Laravel']);

        $response = $this->get(route('books.edit', $book));

        $response->assertOk()
            ->assertSee('Belajar Laravel')
            ->assertSee('Simpan perubahan');
    }

    public function test_store_persists_the_book_with_all_copies_available(): void
    {
        $this->signIn();
        $response = $this->post(route('books.store'), [
            'book_code' => 'BK-NEW-001',
            'title' => 'Dasar Perpustakaan',
            'author' => 'Ayu Lestari',
            'publisher' => 'Pustaka Timur',
            'publication_year' => 2024,
            'total_stock' => 4,
            'shelf_location' => 'Rak A-02',
        ]);

        $response->assertRedirectToRoute('books.index')
            ->assertSessionHas('success', 'Data berhasil ditambahkan.');

        $this->assertDatabaseHas('books', [
            'book_code' => 'BK-NEW-001',
            'title' => 'Dasar Perpustakaan',
            'total_stock' => 4,
            'available_stock' => 4,
        ]);
    }

    public function test_store_rejects_a_duplicate_book_code_without_changing_existing_data(): void
    {
        $this->signIn();
        $book = Book::factory()->create(['book_code' => 'BK-DUP-001']);

        $response = $this->post(route('books.store'), [
            'book_code' => 'BK-DUP-001',
            'title' => 'Judul Lain',
            'author' => 'Penulis Lain',
            'publisher' => 'Penerbit Lain',
            'total_stock' => 2,
        ]);

        $response->assertSessionHasErrors([
            'book_code' => 'Kode buku sudah terdaftar.',
        ]);

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
            'book_code' => 'BK-DUP-001',
        ]);
    }

    public function test_store_requires_the_core_catalog_fields(): void
    {
        $this->signIn();
        $response = $this->post(route('books.store'), []);

        $response->assertSessionHasErrors([
            'book_code' => 'Kode buku wajib diisi.',
            'title' => 'Judul buku wajib diisi.',
            'author' => 'Nama penulis wajib diisi.',
            'publisher' => 'Nama penerbit wajib diisi.',
            'total_stock' => 'Jumlah eksemplar wajib diisi.',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_update_preserves_the_number_of_copies_already_borrowed(): void
    {
        $this->signIn();
        $book = Book::factory()->create([
            'book_code' => 'BK-UPDATE-01',
            'total_stock' => 6,
            'available_stock' => 4,
        ]);

        $response = $this->put(route('books.update', $book), [
            'book_code' => 'BK-UPDATE-01',
            'title' => 'Judul Diperbarui',
            'author' => 'Penulis Diperbarui',
            'publisher' => 'Penerbit Diperbarui',
            'publication_year' => 2023,
            'total_stock' => 8,
            'shelf_location' => 'Rak B-03',
        ]);

        $response->assertRedirectToRoute('books.show', ['book' => $book->id])
            ->assertSessionHas('success', 'Data buku berhasil diperbarui.');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'book_code' => 'BK-UPDATE-01',
            'title' => 'Judul Diperbarui',
            'total_stock' => 8,
            'available_stock' => 6,
        ]);
    }

    public function test_update_rejects_total_stock_below_the_number_of_borrowed_copies(): void
    {
        $this->signIn();
        $book = Book::factory()->create([
            'total_stock' => 6,
            'available_stock' => 4,
        ]);

        $response = $this->put(route('books.update', $book), [
            'book_code' => $book->book_code,
            'title' => $book->title,
            'author' => $book->author,
            'publisher' => $book->publisher,
            'total_stock' => 1,
        ]);

        $response->assertSessionHasErrors([
            'total_stock' => 'Jumlah eksemplar minimal 2.',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'total_stock' => 6,
            'available_stock' => 4,
        ]);
    }

    public function test_update_rejects_a_book_code_owned_by_another_book(): void
    {
        $this->signIn();
        $existingBook = Book::factory()->create(['book_code' => 'BK-OWNER-01']);
        $bookToUpdate = Book::factory()->create(['book_code' => 'BK-OWNER-02']);

        $response = $this->put(route('books.update', $bookToUpdate), [
            'book_code' => 'BK-OWNER-01',
            'title' => $bookToUpdate->title,
            'author' => $bookToUpdate->author,
            'publisher' => $bookToUpdate->publisher,
            'total_stock' => $bookToUpdate->total_stock,
        ]);

        $response->assertSessionHasErrors([
            'book_code' => 'Kode buku sudah terdaftar.',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $existingBook->id,
            'book_code' => 'BK-OWNER-01',
        ]);
        $this->assertDatabaseHas('books', [
            'id' => $bookToUpdate->id,
            'book_code' => 'BK-OWNER-02',
        ]);
    }

    public function test_destroy_removes_the_book_and_returns_to_the_catalog(): void
    {
        $this->signIn();
        $book = Book::factory()->create();

        $response = $this->delete(route('books.destroy', $book));

        $response->assertRedirectToRoute('books.index')
            ->assertSessionHas('success', 'Buku berhasil dihapus dari katalog.');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
