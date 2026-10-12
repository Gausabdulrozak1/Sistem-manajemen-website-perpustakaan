<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_shows_matching_categories_only(): void
    {
        $this->signIn();
        $matchingCategory = Category::factory()->create(['name' => 'Sastra Nusantara']);
        $unmatchedCategory = Category::factory()->create(['name' => 'Ilmu Komputer']);

        $response = $this->get(route('categories.index', ['search' => 'Sastra']));

        $response->assertOk()
            ->assertSee($matchingCategory->name)
            ->assertDontSee($unmatchedCategory->name);
    }

    public function test_store_creates_a_category_and_redirects_to_its_detail(): void
    {
        $this->signIn();
        $response = $this->post(route('categories.store'), [
            'name' => 'Sains Populer',
            'description' => 'Bacaan sains untuk pembaca umum.',
        ]);

        $category = Category::query()->where('name', 'Sains Populer')->firstOrFail();

        $response->assertRedirectToRoute('categories.show', ['category' => $category->id])
            ->assertSessionHas('success', 'Kategori berhasil ditambahkan.');

        $this->assertDatabaseHas('categories', [
            'name' => 'Sains Populer',
            'description' => 'Bacaan sains untuk pembaca umum.',
        ]);
    }

    public function test_store_rejects_a_duplicate_category_name(): void
    {
        $this->signIn();
        Category::factory()->create(['name' => 'Sejarah']);

        $response = $this->post(route('categories.store'), ['name' => 'Sejarah']);

        $response->assertSessionHasErrors([
            'name' => 'Nama kategori sudah digunakan.',
        ]);

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_update_allows_a_category_to_keep_its_own_name(): void
    {
        $this->signIn();
        $category = Category::factory()->create([
            'name' => 'Bahasa',
            'description' => 'Deskripsi lama.',
        ]);

        $response = $this->put(route('categories.update', $category), [
            'name' => 'Bahasa',
            'description' => 'Bahasa dan linguistik.',
        ]);

        $response->assertRedirectToRoute('categories.show', ['category' => $category->id]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Bahasa',
            'description' => 'Bahasa dan linguistik.',
        ]);
    }

    public function test_show_displays_books_assigned_to_the_category(): void
    {
        $this->signIn();
        $category = Category::factory()->create(['name' => 'Teknologi']);
        $book = Book::factory()->create([
            'category_id' => $category->id,
            'title' => 'Mengenal Komputer',
        ]);

        $response = $this->get(route('categories.show', $category));

        $response->assertOk()
            ->assertSee($book->title)
            ->assertSee($book->book_code);
    }

    public function test_destroy_keeps_books_and_clears_their_category(): void
    {
        $this->signIn();
        $category = Category::factory()->create();
        $book = Book::factory()->create(['category_id' => $category->id]);

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirectToRoute('categories.index')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'category_id' => null,
        ]);
    }
}
