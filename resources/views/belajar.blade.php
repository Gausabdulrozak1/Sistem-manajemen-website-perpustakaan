@extends('layouts.app')

@section('title', 'Belajar Laravel')
@section('breadcrumb', 'Belajar Laravel')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Panduan proyek</p>
            <h1>Belajar Laravel dari RuangBaca</h1>
            <p>Ikuti satu permintaan nyata dari alamat browser sampai daftar buku tampil.</p>
        </div>
        <a class="button" href="#pelajaran-route">
            Mulai belajar
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </a>
    </div>

    <section class="content-card" id="pelajaran-route">
        <div class="card-heading">
            <div>
                <h2>Pelajaran 1: bagaimana katalog buku tampil?</h2>
                <p>Empat bagian Laravel bekerja bersama untuk menghasilkan satu halaman.</p>
            </div>
        </div>

        <ol class="grid gap-4 p-5 sm:p-6 lg:grid-cols-2">
            <li class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-5">
                <span class="inline-flex size-9 items-center justify-center rounded-full bg-[#edf3ee] text-sm font-semibold text-[#244735]">01</span>
                <h3 class="mt-4 text-base font-semibold text-[#25362c]">Route menerima alamat</h3>
                <p class="mt-2 text-sm leading-6 text-[#68766d]">Di <code>routes/web.php</code>, resource <code>books</code> menghubungkan permintaan <code>GET /books</code> ke <code>BookController@index</code>.</p>
            </li>
            <li class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-5">
                <span class="inline-flex size-9 items-center justify-center rounded-full bg-[#edf3ee] text-sm font-semibold text-[#244735]">02</span>
                <h3 class="mt-4 text-base font-semibold text-[#25362c]">Controller menyiapkan data</h3>
                <p class="mt-2 text-sm leading-6 text-[#68766d]"><code>BookController@index</code> memeriksa kata kunci pencarian, meminta daftar buku ke model, lalu memilih tampilan <code>books.index</code>.</p>
            </li>
            <li class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-5">
                <span class="inline-flex size-9 items-center justify-center rounded-full bg-[#edf3ee] text-sm font-semibold text-[#244735]">03</span>
                <h3 class="mt-4 text-base font-semibold text-[#25362c]">Model membaca database</h3>
                <p class="mt-2 text-sm leading-6 text-[#68766d]"><code>App\Models\Book</code> mewakili data buku. Eloquent menjalankan query dan memuat kategori tanpa menulis SQL untuk setiap baris di halaman.</p>
            </li>
            <li class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-5">
                <span class="inline-flex size-9 items-center justify-center rounded-full bg-[#edf3ee] text-sm font-semibold text-[#244735]">04</span>
                <h3 class="mt-4 text-base font-semibold text-[#25362c]">Blade membentuk halaman</h3>
                <p class="mt-2 text-sm leading-6 text-[#68766d]"><code>resources/views/books/index.blade.php</code> menampilkan setiap buku, formulir pencarian, dan tautan pagination sebagai HTML.</p>
            </li>
        </ol>

        <div class="border-t border-[#e7ece7] px-5 py-5 sm:px-6">
            <p class="eyebrow">Potongan kode dari empat lapisan</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <article class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-4">
                    <h3 class="text-sm font-semibold text-[#25362c]">1. Route · <code>routes/web.php</code></h3>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-[#22372b] p-3 text-xs leading-5 text-[#eef4ef]"><code>Route::resource('books', BookController::class);</code></pre>
                    <p class="mt-3 text-sm leading-6 text-[#68766d]">Laravel mendaftarkan alamat daftar, tambah, detail, ubah, dan hapus buku.</p>
                </article>
                <article class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-4">
                    <h3 class="text-sm font-semibold text-[#25362c]">2. Controller · <code>BookController</code></h3>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-[#22372b] p-3 text-xs leading-5 text-[#eef4ef]"><code>return view('books.index', compact('books', 'categories', 'search', 'categoryId'));</code></pre>
                    <p class="mt-3 text-sm leading-6 text-[#68766d]"><code>compact</code> mengirim variabel yang sudah disiapkan controller ke tampilan Blade.</p>
                </article>
                <article class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-4">
                    <h3 class="text-sm font-semibold text-[#25362c]">3. Model · <code>App\Models\Book</code></h3>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-[#22372b] p-3 text-xs leading-5 text-[#eef4ef]"><code>public function category(): BelongsTo
{
    return $this-&gt;belongsTo(Category::class);
}</code></pre>
                    <p class="mt-3 text-sm leading-6 text-[#68766d]">Relasi <code>belongsTo</code> berarti setiap buku dapat merujuk ke satu kategori.</p>
                </article>
                <article class="rounded-xl border border-[#e7ece7] bg-[#fbfcfa] p-4">
                    <h3 class="text-sm font-semibold text-[#25362c]">4. Blade · <code>books/index.blade.php</code></h3>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-[#22372b] p-3 text-xs leading-5 text-[#eef4ef]"><code>@verbatim
{{ $book-&gt;title }}
@endverbatim</code></pre>
                    <p class="mt-3 text-sm leading-6 text-[#68766d]">Kurung kurawal menampilkan judul buku dan Blade meng-escape teks untuk mencegah HTML tak tepercaya dijalankan.</p>
                </article>
            </div>
        </div>

        <div class="flex flex-col gap-4 border-t border-[#e7ece7] bg-[#f8faf7] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-base font-semibold text-[#25362c]">Latihan singkat</h3>
                <p class="mt-1 text-sm leading-6 text-[#68766d]">Buka katalog, lalu cocokkan alamat <code>/books</code> dengan route, controller, model, dan file Blade di atas.</p>
                <p class="mt-3 text-sm leading-6 text-[#68766d]"><strong>Hasil yang diharapkan:</strong> alamat menjadi <code>/books</code> dan halaman bertajuk <strong>Koleksi buku</strong>. Tabel buku tampil jika data sudah tersedia; jika belum, muncul pesan katalog kosong.</p>
            </div>
            <a class="button" href="{{ route('books.index') }}">Buka koleksi buku</a>
        </div>
    </section>
@endsection
