<form class="content-card form-card user-form" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($errors->any()) <div class="notice notice-error" role="alert">{{ $errors->first() }}</div> @endif
    <h2 class="form-section-title">Informasi akun</h2>
    <div class="form-grid">
        <div class="field"><label for="name">Nama lengkap <span>*</span></label><input id="name" name="name" value="{{ old('name', $user?->name) }}" maxlength="255" autocomplete="name" required>@error('name')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div class="field"><label for="email">Alamat email <span>*</span></label><input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" maxlength="255" autocomplete="email" required>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div class="field field-full"><label for="role">Peran <span>*</span></label><select id="role" name="role" required><option value="staff" @selected(old('role', $user?->role ?? 'staff') === 'staff')>Petugas — kelola buku, anggota, dan peminjaman</option><option value="admin" @selected(old('role', $user?->role) === 'admin')>Admin — akses penuh termasuk pengelolaan akun</option></select>@error('role')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div class="field"><label for="password">{{ $user ? 'Kata sandi baru' : 'Kata sandi' }} @if ($user)<span>(opsional)</span>@else<span>*</span>@endif</label><input id="password" type="password" name="password" minlength="12" autocomplete="new-password" @required(! $user)><p class="field-hint">Minimal 12 karakter. @if ($user)Kosongkan bila kata sandi tidak diubah.@endif</p>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div class="field"><label for="password_confirmation">Ulangi kata sandi @if ($user)<span>(jika diubah)</span>@else<span>*</span>@endif</label><input id="password_confirmation" type="password" name="password_confirmation" minlength="12" autocomplete="new-password" @required(! $user)>@error('password_confirmation')<p class="field-error">{{ $message }}</p>@enderror</div>
    </div>
    <aside class="loan-policy user-policy"><span class="loan-policy-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.5 2.9 8 7 10 4.1-2 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><strong>Perlindungan akun</strong><p>Hanya admin dapat membuka halaman pengguna. Petugas dapat mengelola kegiatan perpustakaan tanpa mengubah hak akses.</p></div></aside>
    <div class="form-actions"><a class="button button-secondary" href="{{ route('users.index') }}">Batal</a><button class="button" type="submit">{{ $user ? 'Simpan perubahan' : 'Buat akun' }}</button></div>
</form>
