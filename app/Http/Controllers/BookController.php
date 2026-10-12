<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $validatedFilters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ], [
            'search.string' => 'Kata kunci pencarian harus berupa teks.',
            'search.max' => 'Kata kunci pencarian maksimal :max karakter.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
        ]);
        $search = trim((string) ($validatedFilters['search'] ?? ''));
        $categoryId = (string) ($validatedFilters['category_id'] ?? '');

        $books = Book::query()
            ->with('category')
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $pattern = '%'.$search.'%';

                $query->where(static function (Builder $query) use ($pattern): void {
                    $query->where('book_code', 'like', $pattern)
                        ->orWhere('title', 'like', $pattern)
                        ->orWhere('author', 'like', $pattern)
                        ->orWhere('publisher', 'like', $pattern)
                        ->orWhereHas('category', static function (Builder $query) use ($pattern): void {
                            $query->where('name', 'like', $pattern);
                        });
                });
            })
            ->when($categoryId !== '', static function (Builder $query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('books.index', compact('books', 'categories', 'search', 'categoryId'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('books.create', [
            'categories' => $categories,
            'borrowedCopies' => 0,
        ]);
    }

    public function show(Book $book): View
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $borrowedCopies = max(0, $book->total_stock - $book->available_stock);
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('books.edit', compact('book', 'borrowedCopies', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate($this->validationRules(), $this->validationMessages());

        $validatedData['available_stock'] = $validatedData['total_stock'];

        Book::create($validatedData);

        return redirect()->route('books.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $borrowedCopies = max(0, $book->total_stock - $book->available_stock);
        $validatedData = $request->validate(
            $this->validationRules($book, $borrowedCopies),
            $this->validationMessages(),
        );
        $validatedData['available_stock'] = $validatedData['total_stock'] - $borrowedCopies;

        $book->update($validatedData);

        return redirect()->route('books.show', $book)->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->loans()->exists()) {
            return redirect()->route('books.show', $book)
                ->with('error', 'Buku memiliki riwayat peminjaman sehingga tidak dapat dihapus.');
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus dari katalog.');
    }

    /**
     * @return array<string, array<int, mixed>|Unique>
     */
    private function validationRules(?Book $book = null, int $minimumStock = 0): array
    {
        $bookCodeRule = Rule::unique('books', 'book_code');

        if ($book !== null) {
            $bookCodeRule->ignore($book);
        }

        return [
            'book_code' => ['required', 'string', 'max:30', $bookCodeRule],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'total_stock' => ['required', 'integer', 'min:'.$minimumStock],
            'shelf_location' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'book_code.required' => 'Kode buku wajib diisi.',
            'book_code.string' => 'Kode buku harus berupa teks.',
            'book_code.max' => 'Kode buku maksimal :max karakter.',
            'book_code.unique' => 'Kode buku sudah terdaftar.',
            'title.required' => 'Judul buku wajib diisi.',
            'title.string' => 'Judul buku harus berupa teks.',
            'title.max' => 'Judul buku maksimal :max karakter.',
            'author.required' => 'Nama penulis wajib diisi.',
            'author.string' => 'Nama penulis harus berupa teks.',
            'author.max' => 'Nama penulis maksimal :max karakter.',
            'publisher.required' => 'Nama penerbit wajib diisi.',
            'publisher.string' => 'Nama penerbit harus berupa teks.',
            'publisher.max' => 'Nama penerbit maksimal :max karakter.',
            'publication_year.integer' => 'Tahun terbit harus berupa angka.',
            'publication_year.min' => 'Tahun terbit tidak boleh sebelum :min.',
            'publication_year.max' => 'Tahun terbit tidak boleh melebihi :max.',
            'total_stock.required' => 'Jumlah eksemplar wajib diisi.',
            'total_stock.integer' => 'Jumlah eksemplar harus berupa bilangan bulat.',
            'total_stock.min' => 'Jumlah eksemplar minimal :min.',
            'shelf_location.string' => 'Lokasi rak harus berupa teks.',
            'shelf_location.max' => 'Lokasi rak maksimal :max karakter.',
            'category_id.integer' => 'Kategori yang dipilih tidak valid.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
        ];
    }
}
