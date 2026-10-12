<section class="content-card form-card">
    <h2 class="form-section-title">Informasi anggota</h2>
    <form action="{{ $member ? route('members.update', $member) : route('members.store') }}" method="POST">
        @csrf
        @if ($member)
            @method('PUT')
        @endif
        <div class="form-grid">
            <div class="field">
                <label for="member_code">Nomor anggota <span>(wajib)</span></label>
                <input id="member_code" name="member_code" value="{{ old('member_code', $member?->member_code) }}" maxlength="20" placeholder="Contoh: MB-00001" required>
                @error('member_code') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="name">Nama lengkap <span>(wajib)</span></label>
                <input id="name" name="name" value="{{ old('name', $member?->name) }}" maxlength="255" placeholder="Nama anggota" required>
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="email">Alamat email <span>(opsional)</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', $member?->email) }}" maxlength="255" placeholder="nama@email.com" autocomplete="email">
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="phone">Nomor telepon <span>(opsional)</span></label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $member?->phone) }}" maxlength="30" placeholder="+62 812..." autocomplete="tel">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="joined_at">Tanggal bergabung <span>(wajib)</span></label>
                <input id="joined_at" name="joined_at" type="date" value="{{ old('joined_at', $member?->joined_at?->format('Y-m-d') ?? ($joinedAt ?? now()->toDateString())) }}" max="{{ now()->toDateString() }}" required>
                @error('joined_at') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="status">Status keanggotaan <span>(wajib)</span></label>
                <select id="status" name="status" required>
                    <option value="active" @selected(old('status', $member?->status ?? 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $member?->status ?? 'active') === 'inactive')>Tidak aktif</option>
                </select>
                @error('status') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field field-full">
                <label for="address">Alamat <span>(opsional)</span></label>
                <textarea id="address" name="address" rows="3" maxlength="1000" placeholder="Alamat tempat tinggal">{{ old('address', $member?->address) }}</textarea>
                @error('address') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ $member ? route('members.show', $member) : route('members.index') }}">Batal</a>
            <button class="button" type="submit">{{ $member ? 'Simpan perubahan' : 'Daftarkan anggota' }}</button>
        </div>
    </form>
</section>
