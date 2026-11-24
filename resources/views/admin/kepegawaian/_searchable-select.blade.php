{{--
    Dropdown dengan kolom pencarian (dipakai untuk Unit kerja, Instalasi, Jabatan).
    Variabel: $field (nama kolom), $options (daftar pilihan), $current (nilai tersimpan),
              $placeholder (mis. "Pilih unit kerja"), $searchPlaceholder, $emptyText
--}}
@php
    $nilai = old($field, $current);
    $tanda = '— ' . $placeholder . ' —';
@endphp
<div class="searchable-select" data-placeholder="{{ $tanda }}">
    {{-- Native select tersembunyi untuk submit form & validasi --}}
    <select id="{{ $field }}" name="{{ $field }}" class="searchable-select-hidden" tabindex="-1" aria-hidden="true">
        <option value="">{{ $tanda }}</option>
        @foreach ($options as $opsi)
            <option value="{{ $opsi }}" @selected($nilai === $opsi)>{{ $opsi }}</option>
        @endforeach
    </select>

    {{-- Tombol trigger dropdown --}}
    <button type="button" class="searchable-select-trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="searchable-select-label {{ ! $nilai ? 'is-placeholder' : '' }}">{{ $nilai ?: $tanda }}</span>
        <i class="fa-solid fa-chevron-down searchable-select-arrow"></i>
    </button>

    {{-- Panel dropdown mengambang dengan pencarian --}}
    <div class="searchable-select-dropdown" role="listbox">
        <div class="searchable-select-search">
            <i class="fa-solid fa-magnifying-glass searchable-select-search-icon"></i>
            <input type="text" class="searchable-select-input" placeholder="{{ $searchPlaceholder }}" autocomplete="off">
            <button type="button" class="searchable-select-clear" title="Hapus pencarian" style="display: none;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <ul class="searchable-select-list">
            <li class="searchable-select-item {{ ! $nilai ? 'is-selected' : '' }}" data-value="">
                <span>{{ $tanda }}</span>
                @if (! $nilai)
                    <i class="fa-solid fa-check searchable-select-check"></i>
                @endif
            </li>
            @foreach ($options as $opsi)
                @php $dipilih = $nilai === $opsi; @endphp
                <li class="searchable-select-item {{ $dipilih ? 'is-selected' : '' }}" data-value="{{ $opsi }}">
                    <span>{{ $opsi }}</span>
                    @if ($dipilih)
                        <i class="fa-solid fa-check searchable-select-check"></i>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="searchable-select-empty" style="display: none;">
            <i class="fa-regular fa-folder-open"></i> {{ $emptyText }}
        </div>
    </div>
</div>
