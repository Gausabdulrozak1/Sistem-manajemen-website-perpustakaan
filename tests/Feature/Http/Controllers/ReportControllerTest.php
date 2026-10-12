<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_view_or_export_reports(): void
    {
        $this->get(route('reports.index'))->assertRedirectToRoute('login');
        $this->get(route('reports.export'))->assertRedirectToRoute('login');
    }

    public function test_report_summarizes_the_selected_period(): void
    {
        $this->signIn();
        $member = Member::factory()->create(['name' => 'Pembaca Periode Ini']);
        $book = Book::factory()->create(['title' => 'Buku Periode Ini']);
        $inside = Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => today(),
        ]);
        $outsideMember = Member::factory()->create();
        $outsideBook = Book::factory()->create();
        $outside = Loan::factory()->create([
            'member_id' => $outsideMember->id,
            'book_id' => $outsideBook->id,
            'borrowed_at' => today()->subDays(50),
        ]);

        $this->get(route('reports.index', [
            'from' => today()->subDays(14)->toDateString(),
            'to' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee($inside->loan_code)
            ->assertSee('Pembaca Periode Ini')
            ->assertSee('Buku Periode Ini')
            ->assertDontSee($outside->loan_code)
            ->assertDontSee($outsideBook->title);
    }

    public function test_report_rejects_a_end_date_before_the_start_date(): void
    {
        $this->signIn();

        $this->from(route('reports.index'))->get(route('reports.index', [
            'from' => '2026-10-20',
            'to' => '2026-10-01',
        ]))->assertSessionHasErrors('to');
    }

    public function test_export_downloads_a_csv_for_the_selected_period_and_escapes_formulas(): void
    {
        $this->signIn();
        $member = Member::factory()->create(['name' => '=HYPERLINK("https://example.test")']);
        $book = Book::factory()->create(['title' => '+CMD|malicious']);
        $loan = Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => today(),
        ]);

        $response = $this->get(route('reports.export', [
            'from' => today()->toDateString(),
            'to' => today()->toDateString(),
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition');

        $csv = $response->streamedContent();
        $this->assertStringContainsString($loan->loan_code, $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+CMD|malicious", $csv);
        $this->assertStringContainsString('Denda dibayar', $csv);
    }
}
