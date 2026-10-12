<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $validatedFilters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim((string) ($validatedFilters['search'] ?? ''));

        $categories = Category::query()
            ->withCount('books')
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $pattern = '%'.$search.'%';

                $query->where(static function (Builder $query) use ($pattern): void {
                    $query->where('name', 'like', $pattern)
                        ->orWhere('description', 'like', $pattern);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($request->validate($this->validationRules(), $this->validationMessages()));

        return redirect()->route('categories.show', $category)
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category): View
    {
        $books = $category->books()->orderByDesc('id')->paginate(8)->withQueryString();

        return view('categories.show', compact('category', 'books'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($request->validate(
            $this->validationRules($category),
            $this->validationMessages(),
        ));

        return redirect()->route('categories.show', $category)
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori dihapus. Buku di dalamnya tetap tersimpan tanpa kategori.');
    }

    /**
     * @return array<string, array<int, mixed>|Unique>
     */
    private function validationRules(?Category $category = null): array
    {
        $nameRule = Rule::unique('categories', 'name');

        if ($category !== null) {
            $nameRule->ignore($category);
        }

        return [
            'name' => ['required', 'string', 'max:100', $nameRule],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal :max karakter.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'description.string' => 'Deskripsi kategori harus berupa teks.',
            'description.max' => 'Deskripsi kategori maksimal :max karakter.',
        ];
    }
}
