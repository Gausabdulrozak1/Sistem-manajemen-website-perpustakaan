<section class="content-card form-card">
    <h2 class="form-section-title">Informasi kategori</h2>
    <form action="{{ $category ? route('categories.update', $category) : route('categories.store') }}" method="POST">
        @csrf
        @if ($category)
            @method('PUT')
        @endif
        <div class="form-grid">
            <div class="field field-full">
                <label for="name">Nama kategori <span>(wajib)</span></label>
                <input id="name" name="name" value="{{ old('name', $category?->name) }}" maxlength="100" placeholder="Contoh: Fiksi" required>
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field field-full">
                <label for="description">Deskripsi <span>(opsional)</span></label>
                <textarea id="description" name="description" rows="4" maxlength="1000" placeholder="Ceritakan jenis buku dalam kategori ini">{{ old('description', $category?->description) }}</textarea>
                @error('description') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ $category ? route('categories.show', $category) : route('categories.index') }}">Batal</a>
            <button class="button" type="submit">{{ $category ? 'Simpan perubahan' : 'Simpan kategori' }}</button>
        </div>
    </form>
</section>
