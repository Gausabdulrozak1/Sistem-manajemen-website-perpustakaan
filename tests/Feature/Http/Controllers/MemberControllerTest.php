<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Member;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_and_status_filter_return_only_matching_members(): void
    {
        $this->signIn();
        $activeMember = Member::factory()->create([
            'member_code' => 'MB-SEARCH-01',
            'name' => 'Nadia Putri',
            'status' => 'active',
        ]);
        $inactiveMember = Member::factory()->create([
            'member_code' => 'MB-SEARCH-02',
            'name' => 'Nadia Pratama',
            'status' => 'inactive',
        ]);

        $response = $this->get(route('members.index', [
            'search' => 'Nadia',
            'status' => 'active',
        ]));

        $response->assertOk()
            ->assertSee($activeMember->member_code)
            ->assertDontSee($inactiveMember->member_code);
    }

    public function test_store_registers_a_member_and_redirects_to_the_profile(): void
    {
        $this->signIn();
        $response = $this->post(route('members.store'), [
            'member_code' => 'MB-NEW-001',
            'name' => 'Dina Prameswari',
            'email' => 'dina@example.test',
            'phone' => '+628123456789',
            'address' => 'Makassar',
            'joined_at' => '2026-01-10',
            'status' => 'active',
        ]);

        $member = Member::query()->where('member_code', 'MB-NEW-001')->firstOrFail();

        $response->assertRedirectToRoute('members.show', ['member' => $member->id])
            ->assertSessionHas('success', 'Anggota berhasil didaftarkan.');

        $this->assertDatabaseHas('members', [
            'member_code' => 'MB-NEW-001',
            'name' => 'Dina Prameswari',
            'email' => 'dina@example.test',
            'status' => 'active',
        ]);
    }

    public function test_store_rejects_a_future_join_date_and_invalid_email(): void
    {
        $this->signIn();
        $response = $this->post(route('members.store'), [
            'member_code' => 'MB-INVALID-01',
            'name' => 'Nama Uji',
            'email' => 'alamat-bukan-email',
            'joined_at' => now()->addDay()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Alamat email tidak valid.',
            'joined_at' => 'Tanggal bergabung tidak boleh melewati hari ini.',
        ]);

        $this->assertDatabaseCount('members', 0);
    }

    public function test_store_rejects_duplicate_member_codes_and_emails(): void
    {
        $this->signIn();
        $existingMember = Member::factory()->create([
            'member_code' => 'MB-DUP-01',
            'email' => 'reader@example.test',
        ]);

        $response = $this->post(route('members.store'), [
            'member_code' => 'MB-DUP-01',
            'name' => 'Nama Lain',
            'email' => 'reader@example.test',
            'joined_at' => '2026-01-10',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors([
            'member_code' => 'Nomor anggota sudah terdaftar.',
            'email' => 'Alamat email sudah digunakan anggota lain.',
        ]);

        $this->assertDatabaseCount('members', 1);
        $this->assertModelExists($existingMember);
    }

    public function test_update_allows_the_same_email_and_changes_member_status(): void
    {
        $this->signIn();
        $member = Member::factory()->create([
            'member_code' => 'MB-EDIT-01',
            'email' => 'reader@example.test',
            'status' => 'active',
        ]);

        $response = $this->put(route('members.update', $member), [
            'member_code' => 'MB-EDIT-01',
            'name' => 'Nama Diperbarui',
            'email' => 'reader@example.test',
            'phone' => $member->phone,
            'address' => $member->address,
            'joined_at' => $member->joined_at->toDateString(),
            'status' => 'inactive',
        ]);

        $response->assertRedirectToRoute('members.show', ['member' => $member->id]);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Nama Diperbarui',
            'email' => 'reader@example.test',
            'status' => 'inactive',
        ]);
    }

    public function test_show_escapes_member_name_in_the_profile(): void
    {
        $this->signIn();
        $dangerousName = '<script>alert("uji")</script>';
        $member = Member::factory()->create(['name' => $dangerousName]);

        $response = $this->get(route('members.show', $member));

        $response->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee($dangerousName, false);
    }

    public function test_destroy_removes_member_and_redirects_to_the_member_list(): void
    {
        $this->signIn();
        $member = Member::factory()->create();

        $response = $this->delete(route('members.destroy', $member));

        $response->assertRedirectToRoute('members.index')
            ->assertSessionHas('success', 'Anggota berhasil dihapus.');

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
    }
}
