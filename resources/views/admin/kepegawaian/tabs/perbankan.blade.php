@php $profil = $user->profile; @endphp

<form method="POST" action="{{ route('admin.kepegawaian.update-perbankan', $user) }}" class="lb-card">
    @csrf
    @method('PATCH')

    <div class="kp-form-grid">
        <div>
            <label class="lb-label" for="nama_bank">Nama bank</label>
            <select id="nama_bank" name="nama_bank" class="lb-select">
                <option value="">&mdash; Pilih &mdash;</option>
                @foreach (\App\Models\EmployeeProfile::BANK as $opsi)
                    <option value="{{ $opsi }}" @selected(old('nama_bank', $profil?->nama_bank) === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
            @error('nama_bank')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="nomor_rekening">Nomor rekening</label>
            <input type="text" id="nomor_rekening" name="nomor_rekening" class="lb-input" inputmode="numeric"
                   maxlength="35" autocomplete="off"
                   value="{{ old('nomor_rekening', $profil?->nomor_rekening) }}">
            @error('nomor_rekening')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="nama_pemegang_rekening">Nama pemegang rekening</label>
            <input type="text" id="nama_pemegang_rekening" name="nama_pemegang_rekening" class="lb-input" maxlength="150"
                   value="{{ old('nama_pemegang_rekening', $profil?->nama_pemegang_rekening) }}">
            @error('nama_pemegang_rekening')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="kp-help" style="margin-top: 14px;">
        Nomor rekening disimpan terenkripsi di database. Nol di depan tetap dipertahankan.
    </div>

    <div class="kp-actions">
        <button type="submit" class="kp-btn">Simpan</button>
    </div>
</form>