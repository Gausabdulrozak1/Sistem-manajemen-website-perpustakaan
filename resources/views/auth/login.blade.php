@extends('layouts.guest')

@section('title', 'Masuk ke akun admin')

@section('content')
    <main class="auth-shell">
        <section class="auth-story" aria-label="Tentang RuangBaca">
            <a class="brand auth-brand" href="{{ route('login') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M6 8.5c3.8-.9 7.2-.2 10 2.1v14c-2.8-2.3-6.2-3-10-2.1v-14Zm20 0c-3.8-.9-7.2-.2-10 2.1v14c2.8-2.3 6.2-3 10-2.1v-14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M16 10.6v14" stroke="currentColor" stroke-width="1.8"/></svg>
                </span>
                <span class="brand-copy"><strong>RuangBaca</strong><small>Manajemen Perpustakaan</small></span>
            </a>

            <div class="auth-story-copy">
                <span class="auth-kicker"><span></span> Ruang bagi setiap cerita</span>
                <h1>Kelola koleksi.<br><em>Hidupkan cerita.</em></h1>
                <p>Tempat yang tenang untuk mengelola buku, anggota, dan perjalanan setiap peminjaman.</p>
                <div class="auth-illustration" aria-hidden="true">
                    <div class="auth-orbit auth-orbit-one"></div>
                    <div class="auth-orbit auth-orbit-two"></div>
                    <div class="auth-book auth-book-back"><span></span></div>
                    <div class="auth-book auth-book-front"><span></span><i></i></div>
                    <div class="auth-spark auth-spark-one">✦</div>
                    <div class="auth-spark auth-spark-two">✧</div>
                    <div class="auth-leaf">✳</div>
                </div>
            </div>
            <div class="auth-story-footer"><span>BUKU MENYATUKAN KITA</span><span>RUANGBACA · 2026</span></div>
        </section>

        <section class="auth-main">
            <div class="auth-card-wrap">
                <div class="auth-mobile-brand">
                    <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M6 8.5c3.8-.9 7.2-.2 10 2.1v14c-2.8-2.3-6.2-3-10-2.1v-14Zm20 0c-3.8-.9-7.2-.2-10 2.1v14c2.8-2.3 6.2-3 10-2.1v-14Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M16 10.6v14" stroke="currentColor" stroke-width="1.8"/></svg></span>
                    <span><strong>RuangBaca</strong><small>Manajemen Perpustakaan</small></span>
                </div>
                <div class="auth-heading">
                    <span class="auth-overline">SELAMAT DATANG KEMBALI</span>
                    <h2>Masuk ke akun admin</h2>
                    <p>Gunakan email admin untuk melanjutkan ke ruang kerja perpustakaan.</p>
                </div>

                @if ($errors->has('email') && ! old('email'))
                    <div class="auth-alert" role="alert">{{ $errors->first('email') }}</div>
                @endif

                <form class="auth-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="auth-field">
                        <label for="email">Alamat email</label>
                        <div class="auth-input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="14" rx="2"/><path d="m4.5 7 7.5 6 7.5-6"/></svg>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="admin@perpustakaan.id" required autofocus>
                        </div>
                        @error('email') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="auth-field">
                        <div class="auth-label-row"><label for="password">Kata sandi</label></div>
                        <div class="auth-input-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
                            <input id="password" type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                        </div>
                        @error('password') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>
                    <label class="auth-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Ingat saya di perangkat ini</span></label>
                    <button class="auth-submit" type="submit">Masuk ke RuangBaca <span aria-hidden="true">→</span></button>
                </form>
                <p class="auth-secure"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 2.9 8 7 10 4.1-2 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg> Akses khusus untuk pengelola perpustakaan</p>
            </div>
            <footer class="auth-main-footer">© {{ now()->year }} RuangBaca <span>·</span> Dibuat untuk cerita yang terus tumbuh.</footer>
        </section>
    </main>
@endsection
