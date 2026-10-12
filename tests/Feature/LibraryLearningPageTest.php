<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LibraryLearningPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_learning_page_to_login(): void
    {
        $response = $this->get(route('learning'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_read_the_books_request_lesson(): void
    {
        $this->signIn();

        $response = $this->get(route('learning'));

        $response->assertOk()
            ->assertViewIs('belajar')
            ->assertSee('Belajar Laravel dari RuangBaca')
            ->assertSee('Route::resource')
            ->assertSee('compact', false)
            ->assertSee('categoryId', false)
            ->assertSee('belongsTo(Category::class)')
            ->assertSee('{{ $book-&gt;title }}', false)
            ->assertSee('resources/views/books/index.blade.php')
            ->assertSee('Hasil yang diharapkan')
            ->assertSee('Buka koleksi buku');
    }
}
