<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoanControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_open_the_circulation_pages(): void
    {
        $this->get(route('loans.index'))->assertRedirectToRoute('login');
    }

    public function test_admin_can_view_the_circulation_register(): void
    {
        $this->signIn();

        $this->get(route('loans.index'))
            ->assertOk()
            ->assertSee('Sirkulasi buku')
            ->assertSee('Belum ada peminjaman');
    }

    public function test_admin_can_open_the_loan_form_and_transaction_detail(): void
    {
        $this->signIn();
        $member = Member::factory()->create(['name' => 'Pembaca Uji']);
        $book = Book::factory()->create(['title' => 'Kumpulan Cerita Uji']);

        $this->get(route('loans.create'))
            ->assertOk()
            ->assertSee('Pembaca Uji')
            ->assertSee('Kumpulan Cerita Uji');

        $loan = Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $book->id,
        ]);
        $sibling = Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => Book::factory()->create(['title' => 'Kumpulan Cerita Kedua'])->id,
            'transaction_code' => $loan->transaction_code,
        ]);

        $this->get(route('loans.show', $loan))
            ->assertOk()
            ->assertSee($loan->loan_code)
            ->assertSee('Pembaca Uji')
            ->assertSee('Kumpulan Cerita Uji')
            ->assertSee('Kumpulan Cerita Kedua')
            ->assertSee($sibling->loan_code)
            ->assertSee('Catat pengembalian');
    }

    public function test_store_creates_a_loan_sets_due_date_and_decreases_available_stock(): void
    {
        $this->signIn();
        $member = Member::factory()->create();
        $book = Book::factory()->create([
            'total_stock' => 4,
            'available_stock' => 4,
        ]);

        $response = $this->post(route('loans.store'), [
            'member_id' => $member->id,
            'book_ids' => [$book->id],
        ]);

        $loan = Loan::query()->firstOrFail();
        $response->assertRedirectToRoute('loans.show', $loan);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'member_id' => $member->id,
            'book_id' => $book->id,
            'transaction_code' => $loan->transaction_code,
            'returned_at' => null,
        ]);
        $this->assertSame(today()->toDateString(), $loan->borrowed_at->toDateString());
        $this->assertSame(today()->addDays(config('library.loan_period_days'))->toDateString(), $loan->due_at->toDateString());
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'total_stock' => 4,
            'available_stock' => 3,
        ]);
    }

    public function test_store_rejects_a_book_without_available_stock(): void
    {
        $this->signIn();
        $member = Member::factory()->create();
        $book = Book::factory()->create([
            'total_stock' => 2,
            'available_stock' => 0,
        ]);

        $this->from(route('loans.create'))->post(route('loans.store'), [
            'member_id' => $member->id,
            'book_ids' => [$book->id],
        ])->assertSessionHasErrors('book_ids');

        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_stock' => 0]);
    }

    public function test_store_rejects_an_inactive_member(): void
    {
        $this->signIn();
        $member = Member::factory()->create(['status' => 'inactive']);
        $book = Book::factory()->create();

        $this->from(route('loans.create'))->post(route('loans.store'), [
            'member_id' => $member->id,
            'book_ids' => [$book->id],
        ])->assertSessionHasErrors('member_id');

        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_stock' => $book->total_stock]);
    }

    public function test_store_enforces_the_active_loan_limit_for_a_member(): void
    {
        $this->signIn();
        $member = Member::factory()->create();
        Loan::factory()->count(config('library.maximum_active_loans_per_member'))->create([
            'member_id' => $member->id,
        ]);
        $book = Book::factory()->create();

        $this->from(route('loans.create'))->post(route('loans.store'), [
            'member_id' => $member->id,
            'book_ids' => [$book->id],
        ])->assertSessionHasErrors('member_id');

        $this->assertDatabaseCount('loans', config('library.maximum_active_loans_per_member'));
    }

    public function test_one_transaction_can_include_multiple_books_and_updates_each_stock(): void
    {
        $this->signIn();
        $member = Member::factory()->create();
        $firstBook = Book::factory()->create(['total_stock' => 3, 'available_stock' => 3]);
        $secondBook = Book::factory()->create(['total_stock' => 2, 'available_stock' => 2]);

        $response = $this->post(route('loans.store'), [
            'member_id' => $member->id,
            'book_ids' => [$firstBook->id, $secondBook->id],
        ]);

        $loans = Loan::query()->orderBy('id')->get();
        $response->assertRedirectToRoute('loans.show', $loans->first());
        $this->assertCount(2, $loans);
        $this->assertSame($loans[0]->transaction_code, $loans[1]->transaction_code);
        $this->assertDatabaseHas('books', ['id' => $firstBook->id, 'available_stock' => 2]);
        $this->assertDatabaseHas('books', ['id' => $secondBook->id, 'available_stock' => 1]);
    }

    public function test_multi_book_loan_is_atomic_when_one_selected_title_has_no_stock(): void
    {
        $this->signIn();
        $member = Member::factory()->create();
        $availableBook = Book::factory()->create(['available_stock' => 2, 'total_stock' => 2]);
        $unavailableBook = Book::factory()->create(['available_stock' => 0, 'total_stock' => 2]);

        $this->from(route('loans.create'))->post(route('loans.store'), [
            'member_id' => $member->id,
            'book_ids' => [$availableBook->id, $unavailableBook->id],
        ])->assertSessionHasErrors('book_ids');

        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseHas('books', ['id' => $availableBook->id, 'available_stock' => 2]);
        $this->assertDatabaseHas('books', ['id' => $unavailableBook->id, 'available_stock' => 0]);
    }

    public function test_returning_a_book_restores_stock_and_records_no_fine_when_on_time(): void
    {
        $this->signIn();
        $book = Book::factory()->create([
            'total_stock' => 3,
            'available_stock' => 2,
        ]);
        $loan = Loan::factory()->create([
            'book_id' => $book->id,
            'due_at' => today(),
        ]);

        $this->post(route('loans.return', $loan))
            ->assertRedirectToRoute('loans.show', $loan)
            ->assertSessionHas('success', 'Pengembalian dicatat tanpa denda.');

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'late_days' => 0,
            'fine_amount' => 0,
        ]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_stock' => 3]);
    }

    public function test_returning_a_late_book_records_the_daily_fine(): void
    {
        $this->signIn();
        $book = Book::factory()->create([
            'total_stock' => 3,
            'available_stock' => 2,
        ]);
        $loan = Loan::factory()->create([
            'book_id' => $book->id,
            'due_at' => today()->subDays(3),
        ]);

        $this->post(route('loans.return', $loan))
            ->assertRedirectToRoute('loans.show', $loan)
            ->assertSessionHas('success', 'Pengembalian dicatat. Denda keterlambatan Rp3.000.');

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'late_days' => 3,
            'fine_amount' => 3 * config('library.daily_fine_rupiah'),
        ]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_stock' => 3]);
    }

    public function test_returning_the_same_loan_twice_does_not_add_an_extra_copy(): void
    {
        $this->signIn();
        $book = Book::factory()->create([
            'total_stock' => 3,
            'available_stock' => 2,
        ]);
        $loan = Loan::factory()->create(['book_id' => $book->id, 'due_at' => today()]);

        $this->post(route('loans.return', $loan));
        $this->post(route('loans.return', $loan))
            ->assertRedirectToRoute('loans.show', $loan)
            ->assertSessionHas('error', 'Buku pada transaksi ini sudah dikembalikan.');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_stock' => 3]);
    }

    public function test_admin_can_record_partial_fine_payments_until_the_balance_is_paid(): void
    {
        $user = $this->signIn();
        $loan = Loan::factory()->create([
            'returned_at' => now(),
            'fine_amount' => 3000,
        ]);

        $this->post(route('fine-payments.store', $loan), [
            'amount' => 1000,
            'note' => 'Pembayaran tunai',
        ])->assertRedirectToRoute('loans.show', $loan)
            ->assertSessionHas('success', 'Pembayaran Rp1.000 berhasil dicatat.');

        $this->get(route('loans.show', $loan))
            ->assertOk()
            ->assertSee('Rp2.000')
            ->assertSee('Dibayar sebagian')
            ->assertSee('Pembayaran tunai');

        $this->post(route('fine-payments.store', $loan), ['amount' => 2000])
            ->assertRedirectToRoute('loans.show', $loan);

        $this->get(route('loans.show', $loan))
            ->assertOk()
            ->assertSee('Lunas');

        $this->assertDatabaseCount('fine_payments', 2);
        $this->assertDatabaseHas('fine_payments', [
            'loan_id' => $loan->id,
            'received_by' => $user->id,
            'amount' => 2000,
        ]);
    }

    public function test_admin_cannot_collect_more_than_the_remaining_fine(): void
    {
        $this->signIn();
        $loan = Loan::factory()->create([
            'returned_at' => now(),
            'fine_amount' => 3000,
        ]);
        $loan->finePayments()->create(['amount' => 1000, 'paid_at' => now()]);

        $this->from(route('loans.show', $loan))->post(route('fine-payments.store', $loan), [
            'amount' => 2500,
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('fine_payments', 1);
    }

    public function test_book_and_member_with_loan_history_cannot_be_deleted(): void
    {
        $this->signIn();
        $member = Member::factory()->create();
        $book = Book::factory()->create();
        $loan = Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $book->id,
        ]);

        $this->delete(route('books.destroy', $book))
            ->assertRedirectToRoute('books.show', $book)
            ->assertSessionHas('error');
        $this->delete(route('members.destroy', $member))
            ->assertRedirectToRoute('members.show', $member)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
        $this->assertDatabaseHas('members', ['id' => $member->id]);
    }
}
