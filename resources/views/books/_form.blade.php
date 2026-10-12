<section class="content-card form-card">
    <h2 class="form-section-title">Informasi buku</h2>
    <form action="{{ $book ? route('books.update', $book) : route('books.store') }}" method="POST">
        @csrf
        @if ($book)
            @method('PUT')
        @endif
        <div class="form-grid">
            <div class="field">
                <label for="book_code">Kode buku <span>(wajib)</span></label>
                <input id="book_code" name="book_code" value="{{ old('book_code', $book?->book_code) }}" maxlength="30" placeholder="Contoh: BK-001" autocomplete="off" required>
                @error('book_code') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="title">Judul buku <span>(wajib)</span></label>
                <input id="title" name="title" value="{{ old('title', $book?->title) }}" maxlength="255" placeholder="Masukkan judul buku" required>
                @error('title') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="author">Penulis <span>(wajib)</span></label>
                <input id="author" name="author" value="{{ old('author', $book?->author) }}" maxlength="255" placeholder="Nama penulis" required>
                @error('author') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="publisher">Penerbit <span>(wajib)</span></label>
                <input id="publisher" name="publisher" value="{{ old('publisher', $book?->publisher) }}" maxlength="255" placeholder="Nama penerbit" required>
                @error('publisher') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="category_id">Kategori <span>(opsional)</span></label>
                <select id="category_id" name="category_id">
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $book?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="publication_year">Tahun terbit <span>(opsional)</span></label>
                <input id="publication_year" name="publication_year" type="number" min="1900" max="{{ now()->year }}" value="{{ old('publication_year', $book?->publication_year) }}" placeholder="Contoh: 2024">
                @error('publication_year') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="total_stock">Jumlah eksemplar <span>(wajib)</span></label>
                <input id="total_stock" name="total_stock" type="number" min="{{ $borrowedCopies }}" value="{{ old('total_stock', $book?->total_stock ?? 1) }}" required>
                @if ($borrowedCopies > 0)
                    <p class="field-hint">Minimal {{ $borrowedCopies }} eksemplar karena masih tercatat dipinjam.</p>
                @endif
                @error('total_stock') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field field-full">
                <label for="shelf_location">Lokasi rak <span>(opsional)</span></label>
                <input id="shelf_location" name="shelf_location" value="{{ old('shelf_location', $book?->shelf_location) }}" maxlength="255" placeholder="Contoh: Rak A · Baris 02">
                @error('shelf_location') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ $book ? route('books.show', $book) : route('books.index') }}">Batal</a>
            <button class="button" type="submit">{{ $book ? 'Simpan perubahan' : 'Simpan buku' }}</button>
        </div>
    </form>
</section>
