@extends('layouts.app')

@section('title', $user->name)

@section('content')
@include('admin.kepegawaian._styles')

<div class="kp-page">

    <div class="kp-head">
        <a href="{{ route('admin.kepegawaian.index') }}" class="kp-back">&larr; Daftar pegawai</a>
    </div>

    {{-- Header profil --}}
    <div class="lb-card kp-profile">
        <div class="kp-avatar kp-avatar--lg">{{ $user->inisial() }}</div>

        <div class="kp-row-main">
            <div class="kp-title">{{ $user->name }}</div>
            <div class="kp-meta">
                {{ collect([$user->employee_unit, $user->employee_category])->filter()->implode(' · ') ?: 'Unit dan kategori belum diisi' }}
            </div>

            <div class="kp-chips">
                @if ($user->isAdmin())
                    <span class="kp-chip kp-chip--admin">Admin</span>
                @endif

                @if ($user->email)
                    <span class="kp-chip kp-chip--ok">Bisa login</span>
                @else
                    <span class="kp-chip kp-chip--wait">Belum ada email</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Tab detail --}}
    <div class="kp-dtabs">
        <a href="{{ route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'overview']) }}"
           class="kp-dtab {{ $tab === 'overview' ? 'is-active' : '' }}">
            <i class="fa-solid fa-house"></i> Overview
        </a>

        <a href="{{ route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'kontak']) }}"
           class="kp-dtab {{ $tab === 'kontak' ? 'is-active' : '' }}">
            <i class="fa-solid fa-phone"></i> Kontak
        </a>

        {{-- Tab yang akan dibuat di tahap berikutnya --}}
        @foreach (['Data Pribadi', 'Pekerjaan', 'Perbankan', 'Berkas', 'Payroll (Gaji)', 'Sistem'] as $nama)
            <span class="kp-dtab is-off" title="Segera hadir">{{ $nama }}</span>
        @endforeach
    </div>

    @if ($tab === 'overview')

        {{-- ========== TAB OVERVIEW ========== --}}
        <form method="POST" action="{{ route('admin.kepegawaian.update-overview', $user) }}" class="lb-card">
            @csrf
            @method('PATCH')

            <div class="kp-form-grid">
                <div>
                    <label class="lb-label" for="name">Nama lengkap</label>
                    <input type="text" id="name" name="name" class="lb-input" required maxlength="255"
                           value="{{ old('name', $user->name) }}">
                    @error('name')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                    <div class="kp-help">Lengkapi dengan gelar. Nama dari mesin sidik jari dibatasi 24 karakter.</div>
                </div>

                <div>
                    <label class="lb-label" for="employee_category">Kategori</label>
                    <select id="employee_category" name="employee_category" class="lb-select">
                        <option value="">&mdash; Pilih &mdash;</option>
                        @foreach (\App\Models\User::KATEGORI as $kategori)
                            <option value="{{ $kategori }}"
                                @selected(old('employee_category', $user->employee_category) === $kategori)>
                                {{ $kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_category')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="lb-label" for="employee_unit">Unit kerja / divisi</label>
                    <input type="text" id="employee_unit" name="employee_unit" class="lb-input"
                           list="daftar-unit" maxlength="100" placeholder="Ketik atau pilih unit"
                           value="{{ old('employee_unit', $user->employee_unit) }}">
                    <datalist id="daftar-unit">
                        @foreach ($daftarUnit as $unit)
                            <option value="{{ $unit }}">
                        @endforeach
                    </datalist>
                    @error('employee_unit')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Info (tidak bisa diedit di sini) --}}
            <div class="kp-info">
                <div>
                    <div class="kp-info-label">ID mesin sidik jari</div>
                    <div class="kp-info-value">{{ $user->fingerprint_id ?? '-' }}</div>
                </div>

                <div>
                    <div class="kp-info-label">Hak akses</div>
                    <div class="kp-info-value">{{ $user->isAdmin() ? 'Admin' : 'Pegawai' }}</div>
                </div>

                <div>
                    <div class="kp-info-label">Akun Google</div>
                    <div class="kp-info-value">{{ $user->google_id ? 'Sudah pernah login' : 'Belum pernah login' }}</div>
                </div>
            </div>

            <div class="kp-actions">
                <button type="submit" class="kp-btn">Simpan</button>
            </div>
        </form>

    @else

        {{-- ========== TAB KONTAK ========== --}}
        <form method="POST" action="{{ route('admin.kepegawaian.update-kontak', $user) }}" class="lb-card">
            @csrf
            @method('PATCH')

            <div class="kp-form-grid">
                <div>
                    <label class="lb-label" for="email">Email (untuk login Google)</label>
                    <input type="email" id="email" name="email" class="lb-input" maxlength="255"
                           autocomplete="off" placeholder="nama@gmail.com"
                           value="{{ old('email', $user->email) }}">
                    @error('email')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                    <div class="kp-help">
                        Pegawai hanya bisa masuk ke sistem kalau emailnya terdaftar di sini.
                        Selama kosong, pegawai belum bisa login.
                    </div>
                </div>

                <div>
                    <label class="lb-label" for="phone">Nomor HP / WhatsApp</label>
                    <input type="tel" id="phone" name="phone" class="lb-input" maxlength="20"
                           placeholder="08xxxxxxxxxx"
                           value="{{ old('phone', $user->profile?->phone) }}">
                    @error('phone')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="kp-actions">
                <button type="submit" class="kp-btn">Simpan</button>
            </div>
        </form>

    @endif

</div>
@endsection