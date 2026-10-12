<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $validatedFilters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $search = trim((string) ($validatedFilters['search'] ?? ''));
        $status = $validatedFilters['status'] ?? '';

        $members = Member::query()
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $pattern = '%'.$search.'%';

                $query->where(static function (Builder $query) use ($pattern): void {
                    $query->where('member_code', 'like', $pattern)
                        ->orWhere('name', 'like', $pattern)
                        ->orWhere('email', 'like', $pattern)
                        ->orWhere('phone', 'like', $pattern);
                });
            })
            ->when($status !== '', static function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('members.index', compact('members', 'search', 'status'));
    }

    public function create(): View
    {
        return view('members.create', ['joinedAt' => now()->toDateString()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = Member::create($request->validate($this->validationRules(), $this->validationMessages()));

        return redirect()->route('members.show', $member)
            ->with('success', 'Anggota berhasil didaftarkan.');
    }

    public function show(Member $member): View
    {
        $loanHistory = $member->loans()->with('book')->latest('borrowed_at')->latest('id')->limit(10)->get();

        return view('members.show', compact('member', 'loanHistory'));
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $member->update($request->validate(
            $this->validationRules($member),
            $this->validationMessages(),
        ));

        return redirect()->route('members.show', $member)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->loans()->exists()) {
            return redirect()->route('members.show', $member)
                ->with('error', 'Anggota memiliki riwayat peminjaman sehingga tidak dapat dihapus.');
        }

        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, mixed>|Unique>
     */
    private function validationRules(?Member $member = null): array
    {
        $memberCodeRule = Rule::unique('members', 'member_code');
        $emailRule = Rule::unique('members', 'email');

        if ($member !== null) {
            $memberCodeRule->ignore($member);
            $emailRule->ignore($member);
        }

        return [
            'member_code' => ['required', 'string', 'max:20', $memberCodeRule],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'joined_at' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'member_code.required' => 'Nomor anggota wajib diisi.',
            'member_code.unique' => 'Nomor anggota sudah terdaftar.',
            'member_code.max' => 'Nomor anggota maksimal :max karakter.',
            'name.required' => 'Nama anggota wajib diisi.',
            'name.max' => 'Nama anggota maksimal :max karakter.',
            'email.email' => 'Alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan anggota lain.',
            'email.max' => 'Alamat email maksimal :max karakter.',
            'phone.max' => 'Nomor telepon maksimal :max karakter.',
            'address.max' => 'Alamat maksimal :max karakter.',
            'joined_at.required' => 'Tanggal bergabung wajib diisi.',
            'joined_at.date' => 'Tanggal bergabung tidak valid.',
            'joined_at.before_or_equal' => 'Tanggal bergabung tidak boleh melewati hari ini.',
            'status.required' => 'Status anggota wajib dipilih.',
            'status.in' => 'Status anggota tidak valid.',
        ];
    }
}
