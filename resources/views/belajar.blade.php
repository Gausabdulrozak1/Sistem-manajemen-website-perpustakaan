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
            <p class="eyebrow">Contoh dari proyek</p>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-[#22372b] p-4 text-sm text-[#eef4ef]"><code>Route::resource('books', BookController::class);</code></pre>
            <p class="mt-3 text-sm leading-6 text-[#68766d]">Baris ini mendaftarkan alamat daftar, tambah, detail, ubah, dan hapus buku. Untuk latihan pertama, kita fokus pada <code>books.index</code>.</p>
        </div>

        <div class="flex flex-col gap-4 border-t border-[#e7ece7] bg-[#f8faf7] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 class="text-base font-semibold text-[#25362c]">Latihan singkat</h3>
                <p class="mt-1 text-sm leading-6 text-[#68766d]">Buka katalog, lalu cocokkan alamat <code>/books</code> dengan route, controller, model, dan file Blade di atas.</p>
            </div>
            <a class="button" href="{{ route('books.index') }}">Buka koleksi buku</a>
        </div>
    </section>
@endsection
