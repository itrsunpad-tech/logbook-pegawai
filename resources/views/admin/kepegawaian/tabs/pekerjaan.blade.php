@php $profil = $user->profile; @endphp

<form method="POST" action="{{ route('admin.kepegawaian.update-pekerjaan', $user) }}" class="lb-card">
    @csrf
    @method('PATCH')

    <div class="kp-form-grid">
        <div>
            <label class="lb-label" for="jabatan">Jabatan / pekerjaan</label>
            <input type="text" id="jabatan" name="jabatan" class="lb-input" maxlength="100"
                   value="{{ old('jabatan', $profil?->jabatan) }}">
            @error('jabatan')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="status_kepegawaian">Status kepegawaian</label>
            <select id="status_kepegawaian" name="status_kepegawaian" class="lb-select">
                <option value="">&mdash; Pilih &mdash;</option>
                @foreach (\App\Models\EmployeeProfile::STATUS_KEPEGAWAIAN as $opsi)
                    <option value="{{ $opsi }}" @selected(old('status_kepegawaian', $profil?->status_kepegawaian) === $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
            @error('status_kepegawaian')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="lb-label" for="tanggal_mulai_kerja">Tanggal mulai kerja</label>
            <input type="date" id="tanggal_mulai_kerja" name="tanggal_mulai_kerja" class="lb-input"
                   value="{{ old('tanggal_mulai_kerja', $profil?->tanggal_mulai_kerja?->format('Y-m-d')) }}">
            @error('tanggal_mulai_kerja')
                <div class="kp-error">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="kp-info">
        <div>
            <div class="kp-info-label">Unit kerja / divisi</div>
            <div class="kp-info-value">{{ $user->employee_unit ?: '-' }}</div>
        </div>

        <div>
            <div class="kp-info-label">Kategori</div>
            <div class="kp-info-value">{{ $user->employee_category ?: '-' }}</div>
        </div>
    </div>

    <div class="kp-help" style="margin-top: 10px;">
        Unit kerja dan kategori diubah di tab Overview.
    </div>

    <div class="kp-actions">
        <button type="submit" class="kp-btn">Simpan</button>
    </div>
</form>