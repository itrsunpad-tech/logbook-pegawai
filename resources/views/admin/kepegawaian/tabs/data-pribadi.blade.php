@php $profil = $user->profile; @endphp

<form method="POST" action="{{ route('admin.kepegawaian.update-data-pribadi', $user) }}" class="lb-card">
    @csrf
    @method('PATCH')

    <div class="kp-form-grid">
        <div>
            <label class="lb-label" for="nik">NIK</label>
            <input type="text" id="nik" name="nik" class="lb-input" inputmode="numeric"
                   maxlength="25" autocomplete="off" placeholder="16 digit angka"
                   value="{{ old('nik', $profil?->nik) }}">
            @error('nik')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="npwp">NPWP</label>
            <input type="text" id="npwp" name="npwp" class="lb-input" inputmode="numeric"
                   maxlength="30" autocomplete="off" placeholder="15 atau 16 digit angka"
                   value="{{ old('npwp', $profil?->npwp) }}">
            @error('npwp')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="tempat_lahir">Tempat lahir</label>
            <input type="text" id="tempat_lahir" name="tempat_lahir" class="lb-input" maxlength="100"
                   value="{{ old('tempat_lahir', $profil?->tempat_lahir) }}">
            @error('tempat_lahir')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="tanggal_lahir">Tanggal lahir</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="lb-input"
                   value="{{ old('tanggal_lahir', $profil?->tanggal_lahir?->format('Y-m-d')) }}">
            @error('tanggal_lahir')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="jenis_kelamin">Jenis kelamin</label>
            <select id="jenis_kelamin" name="jenis_kelamin" class="lb-select">
                <option value="">&mdash; Pilih &mdash;</option>
                @foreach (\App\Models\EmployeeProfile::JENIS_KELAMIN as $opsi)
                    <option value="{{ $opsi }}" @selected(old('jenis_kelamin', $profil?->jenis_kelamin) === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
            @error('jenis_kelamin')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="agama">Agama</label>
            <select id="agama" name="agama" class="lb-select">
                <option value="">&mdash; Pilih &mdash;</option>
                @foreach (\App\Models\EmployeeProfile::AGAMA as $opsi)
                    <option value="{{ $opsi }}" @selected(old('agama', $profil?->agama) === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
            @error('agama')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="pendidikan_terakhir">Pendidikan terakhir</label>
            <select id="pendidikan_terakhir" name="pendidikan_terakhir" class="lb-select">
                <option value="">&mdash; Pilih &mdash;</option>
                @foreach (\App\Models\EmployeeProfile::PENDIDIKAN as $opsi)
                    <option value="{{ $opsi }}" @selected(old('pendidikan_terakhir', $profil?->pendidikan_terakhir) === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
            @error('pendidikan_terakhir')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="status_nikah">Status nikah</label>
            <select id="status_nikah" name="status_nikah" class="lb-select">
                <option value="">&mdash; Pilih &mdash;</option>
                @foreach (\App\Models\EmployeeProfile::STATUS_NIKAH as $opsi)
                    <option value="{{ $opsi }}" @selected(old('status_nikah', $profil?->status_nikah) === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
            @error('status_nikah')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="jumlah_anak">Jumlah anak</label>
            <input type="number" id="jumlah_anak" name="jumlah_anak" class="lb-input" min="0" max="20"
                   value="{{ old('jumlah_anak', $profil?->jumlah_anak) }}">
            @error('jumlah_anak')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="nama_ibu_kandung">Nama ibu kandung</label>
            <input type="text" id="nama_ibu_kandung" name="nama_ibu_kandung" class="lb-input" maxlength="150"
                   value="{{ old('nama_ibu_kandung', $profil?->nama_ibu_kandung) }}">
            @error('nama_ibu_kandung')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="alamat_domisili">Alamat domisili</label>
            <textarea id="alamat_domisili" name="alamat_domisili" class="lb-textarea" maxlength="500">{{ old('alamat_domisili', $profil?->alamat_domisili) }}</textarea>
            @error('alamat_domisili')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="alamat_ktp">Alamat sesuai KTP</label>
            <textarea id="alamat_ktp" name="alamat_ktp" class="lb-textarea" maxlength="500">{{ old('alamat_ktp', $profil?->alamat_ktp) }}</textarea>
            @error('alamat_ktp')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="kp-help" style="margin-top: 14px;">
        NIK dan NPWP disimpan terenkripsi di database. Titik, spasi, atau strip akan dibuang otomatis, cukup ketik angkanya.
    </div>

    <div class="kp-actions">
        <button type="submit" class="kp-btn">Simpan</button>
    </div>
</form>