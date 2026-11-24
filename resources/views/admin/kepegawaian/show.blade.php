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
                {{ $user->employee_instalasi ?: 'Instalasi belum diisi' }}
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

        <a href="{{ route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'data-pribadi']) }}"
           class="kp-dtab {{ $tab === 'data-pribadi' ? 'is-active' : '' }}">
            <i class="fa-solid fa-id-card"></i> Data Pribadi
        </a>

        <a href="{{ route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'pekerjaan']) }}"
           class="kp-dtab {{ $tab === 'pekerjaan' ? 'is-active' : '' }}">
            <i class="fa-solid fa-briefcase"></i> Pekerjaan
        </a>

        <a href="{{ route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'perbankan']) }}"
           class="kp-dtab {{ $tab === 'perbankan' ? 'is-active' : '' }}">
            <i class="fa-solid fa-building-columns"></i> Perbankan
        </a>

        {{-- Tab yang akan dibuat di tahap berikutnya --}}
        @foreach (['Berkas', 'Payroll (Gaji)', 'Sistem'] as $nama)
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
                    <label class="lb-label" for="employee_unit">Unit kerja / divisi</label>
                    @include('admin.kepegawaian._searchable-select', [
                        'field'             => 'employee_unit',
                        'options'           => $daftarUnit,
                        'current'           => $user->employee_unit,
                        'placeholder'       => 'Pilih unit kerja',
                        'searchPlaceholder' => 'Cari unit kerja...',
                        'emptyText'         => 'Tidak ada unit kerja yang cocok',
                    ])
                    <div class="kp-help">Pilihan jadwal logbook pegawai mengikuti unit ini.</div>
                    @error('employee_unit')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="lb-label" for="employee_instalasi">Instalasi</label>
                    @include('admin.kepegawaian._searchable-select', [
                        'field'             => 'employee_instalasi',
                        'options'           => $daftarInstalasi,
                        'current'           => $user->employee_instalasi,
                        'placeholder'       => 'Pilih instalasi',
                        'searchPlaceholder' => 'Cari instalasi...',
                        'emptyText'         => 'Tidak ada instalasi yang cocok',
                    ])
                    @error('employee_instalasi')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="lb-label" for="employee_jabatan">Jabatan</label>
                    @include('admin.kepegawaian._searchable-select', [
                        'field'             => 'employee_jabatan',
                        'options'           => $daftarJabatan,
                        'current'           => $user->employee_jabatan,
                        'placeholder'       => 'Pilih jabatan',
                        'searchPlaceholder' => 'Cari jabatan...',
                        'emptyText'         => 'Tidak ada jabatan yang cocok',
                    ])
                    @error('employee_jabatan')
                        <div class="kp-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="lb-label" for="work_hour_type">Jenis jam kerja</label>
                    <select id="work_hour_type" name="work_hour_type" class="lb-select">
                        <option value="">&mdash; Pilih jenis &mdash;</option>
                        @foreach (\App\Models\User::JENIS_JAM_KERJA as $jenis)
                            <option value="{{ $jenis }}"
                                @selected(old('work_hour_type', $user->work_hour_type) === $jenis)>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('work_hour_type')
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

    @elseif ($tab === 'kontak')

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

        @elseif ($tab === 'data-pribadi')
        @include('admin.kepegawaian.tabs.data-pribadi')

    @elseif ($tab === 'pekerjaan')
        @include('admin.kepegawaian.tabs.pekerjaan')

    @elseif ($tab === 'perbankan')
        @include('admin.kepegawaian.tabs.perbankan')

    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.searchable-select').forEach(function (root) {
    const hiddenSelect = root.querySelector('.searchable-select-hidden');
    const trigger = root.querySelector('.searchable-select-trigger');
    const triggerLabel = root.querySelector('.searchable-select-label');
    const dropdown = root.querySelector('.searchable-select-dropdown');
    const searchInput = root.querySelector('.searchable-select-input');
    const clearBtn = root.querySelector('.searchable-select-clear');
    const items = Array.from(root.querySelectorAll('.searchable-select-item'));
    const emptyState = root.querySelector('.searchable-select-empty');
    const list = root.querySelector('.searchable-select-list');

    let highlightedIndex = -1;

    function openDropdown() {
        document.querySelectorAll('.searchable-select.is-open').forEach(el => {
            if (el !== root) el.classList.remove('is-open');
        });

        root.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        searchInput.value = '';
        clearBtn.style.display = 'none';
        filterOptions('');
        setTimeout(() => searchInput.focus(), 60);
    }

    function closeDropdown() {
        root.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        clearHighlight();
    }

    function selectOption(val, label) {
        hiddenSelect.value = val;
        triggerLabel.textContent = label || root.dataset.placeholder;
        if (val) {
            triggerLabel.classList.remove('is-placeholder');
        } else {
            triggerLabel.classList.add('is-placeholder');
        }

        items.forEach(item => {
            const isMatch = item.dataset.value === val;
            item.classList.toggle('is-selected', isMatch);
            const oldCheck = item.querySelector('.searchable-select-check');
            if (oldCheck) oldCheck.remove();
            if (isMatch) {
                const check = document.createElement('i');
                check.className = 'fa-solid fa-check searchable-select-check';
                item.appendChild(check);
            }
        });

        hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
        closeDropdown();
        trigger.focus();
    }

    function filterOptions(term) {
        const query = term.trim().toLowerCase();
        let visibleCount = 0;
        clearHighlight();

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            const matches = query === '' || text.includes(query);
            item.style.display = matches ? 'flex' : 'none';
            if (matches) visibleCount++;
        });

        if (visibleCount === 0) {
            list.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            list.style.display = 'block';
            emptyState.style.display = 'none';
        }

        clearBtn.style.display = query.length > 0 ? 'flex' : 'none';
    }

    function getVisibleItems() {
        return items.filter(item => item.style.display !== 'none');
    }

    function clearHighlight() {
        items.forEach(i => i.classList.remove('is-highlighted'));
        highlightedIndex = -1;
    }

    function highlightItem(index) {
        const visible = getVisibleItems();
        if (visible.length === 0) return;

        items.forEach(i => i.classList.remove('is-highlighted'));
        highlightedIndex = Math.max(0, Math.min(index, visible.length - 1));
        const target = visible[highlightedIndex];
        if (target) {
            target.classList.add('is-highlighted');
            target.scrollIntoView({ block: 'nearest' });
        }
    }

    // Toggle on trigger click
    trigger.addEventListener('click', function (e) {
        e.preventDefault();
        if (root.classList.contains('is-open')) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Option item click
    items.forEach(item => {
        item.addEventListener('click', function () {
            selectOption(this.dataset.value, this.querySelector('span')?.textContent.trim() || this.dataset.value);
        });
    });

    // Real-time search
    searchInput.addEventListener('input', function () {
        filterOptions(this.value);
    });

    // Clear search
    clearBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        searchInput.value = '';
        filterOptions('');
        searchInput.focus();
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function (e) {
        const visible = getVisibleItems();
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightItem(highlightedIndex + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightItem(highlightedIndex - 1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && highlightedIndex < visible.length) {
                const target = visible[highlightedIndex];
                selectOption(target.dataset.value, target.querySelector('span')?.textContent.trim() || target.dataset.value);
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeDropdown();
            trigger.focus();
        }
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) {
            closeDropdown();
        }
    });
  });
});
</script>
@endpush
