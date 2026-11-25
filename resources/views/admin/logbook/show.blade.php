@extends('layouts.app')

@section('title', 'Review Logbook - ' . $user->name)

@section('content')
@include('admin.logbook._styles')

<div class="acc-page">

    <div class="acc-head">
        <div>
            <h1 class="acc-title">{{ $user->name }}</h1>
            <p class="acc-sub">
                @if ($user->employee_unit)
                    <i class="fa-solid fa-building"></i> {{ $user->employee_unit }} &middot;
                @endif
                Menampilkan entri: {{ $status === 'semua' ? 'semua status' : $status }}
            </p>
        </div>

        <a href="{{ route('admin.logbook.index', request()->except('page')) }}" class="acc-back">
            &larr; Kembali ke daftar pegawai
        </a>
    </div>

    {{-- Error validasi (mis. alasan penolakan kosong) --}}
    @if ($errors->any())
        <div class="acc-alert" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('success'))
        <div class="acc-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</div>
    @endif

    {{-- Riwayat semua status dari pegawai ini dapat ditelusuri melalui tab Semua. --}}
    @php
        $tabs = [
            \App\Models\Logbook::STATUS_MENUNGGU => $riwayatStatus->get(\App\Models\Logbook::STATUS_MENUNGGU, 0),
            \App\Models\Logbook::STATUS_DISETUJUI => $riwayatStatus->get(\App\Models\Logbook::STATUS_DISETUJUI, 0),
            \App\Models\Logbook::STATUS_DITOLAK => $riwayatStatus->get(\App\Models\Logbook::STATUS_DITOLAK, 0),
            'semua' => $riwayatStatus->sum(),
        ];
    @endphp
    <nav class="acc-tabs" aria-label="Filter riwayat status logbook">
        @foreach ($tabs as $nama => $total)
            <a href="{{ route('admin.logbook.pegawai', array_merge(request()->except(['page', 'status']), ['user' => $user->id, 'status' => $nama])) }}"
               class="acc-tab {{ $status === $nama ? 'is-active' : '' }}">
                {{ $nama === 'semua' ? 'Semua · Riwayat' : $nama }} <b>{{ $total }}</b>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.logbook.pegawai', $user) }}" class="lb-card" id="acc-review-filter">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="acc-filter">
            <div>
                <label class="lb-label" for="jenis">Jenis logbook</label>
                <select id="jenis" name="jenis" class="lb-select">
                    <option value="">Semua jenis</option>
                    @foreach (['harian' => 'Harian', 'lembur' => 'Lembur', 'oncall' => 'On Call'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="lb-label" for="dari">Dari tanggal</label><input type="date" id="dari" name="dari" value="{{ request('dari') }}" class="lb-input"></div>
            <div><label class="lb-label" for="sampai">Sampai tanggal</label><input type="date" id="sampai" name="sampai" value="{{ request('sampai') }}" class="lb-input"></div>
            <a href="{{ route('admin.logbook.pegawai', ['user' => $user->id, 'status' => $status]) }}" class="acc-btn acc-btn--ghost acc-reset">Reset</a>
        </div>
    </form>

    <div class="acc-bulk-select" id="bulk-select-row"><label><input type="checkbox" id="select-all-logbooks"> <span id="select-all-label">Pilih semua menunggu di halaman ini</span></label><button type="button" id="clear-logbooks" hidden>Kosongkan</button></div>
    <form method="POST" action="{{ route('admin.logbook.setujui-terpilih', $user) }}" id="bulk-approve-form" hidden>@csrf</form>

    {{-- Daftar logbook pegawai ini --}}
    @forelse ($logbooks as $lb)
        @php
            $jam = ($lb->jam_mulai && $lb->jam_selesai)
                ? substr($lb->jam_mulai, 0, 5) . ' - ' . substr($lb->jam_selesai, 0, 5)
                : null;

            $jenisLabel = $lb->is_cuti ? 'Cuti' : ($lb->is_izin_sakit ? 'Izin/Sakit' : (['harian' => 'Harian', 'lembur' => 'Lembur', 'oncall' => 'On Call'][$lb->jenis] ?? $lb->jenis));

            $kelasStatus = match ($lb->status) {
                \App\Models\Logbook::STATUS_DISETUJUI => 'st--ok',
                \App\Models\Logbook::STATUS_DITOLAK   => 'st--no',
                default                               => 'st--wait',
            };

            $detail = $lb->is_cuti
                ? 'Rentang cuti: ' . $lb->tanggal->locale('id')->translatedFormat('d F Y') . ' – ' . $lb->cuti_selesai?->locale('id')->translatedFormat('d F Y')
                : ($lb->is_izin_sakit ? 'Izin/Sakit' : collect([$lb->shift?->nama ? 'Shift: ' . $lb->shift->nama : null, $jam])->filter()->implode(' | '));
        @endphp

        <div class="lb-card acc-item" data-review-card>
            @if ($lb->status === \App\Models\Logbook::STATUS_MENUNGGU && !in_array($lb->status_banding, [\App\Models\Logbook::BANDING_DISETUJUI, \App\Models\Logbook::BANDING_DITOLAK], true))
                <label class="acc-select-item"><input type="checkbox" name="logbook_ids[]" value="{{ $lb->id }}" form="bulk-approve-form" data-logbook-choice data-is-cuti="{{ $lb->is_cuti ? '1' : '0' }}"> Pilih logbook</label>
            @endif

            <div class="acc-top">
                <div class="acc-date">
                    {{ $lb->is_cuti
                        ? $lb->tanggal->format('d/m/Y') . ' – ' . $lb->cuti_selesai?->format('d/m/Y')
                        : $lb->tanggal->format('d/m/Y') }}
                </div>

                <div class="acc-badges">
                    <span class="acc-chip">{{ $jenisLabel }}</span>
                    @if ($lb->is_wfh)
                        <span class="acc-chip">WFH</span>
                    @endif
                    @if ($lb->is_dinas_luar)
                        <span class="acc-chip">Dinas Luar</span>
                    @endif
                    <span class="st {{ $kelasStatus }}">{{ $lb->status }}</span>
                </div>
            </div>

            <div class="acc-submitted">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                Dikirim {{ $lb->created_at?->format('d/m/Y, H:i') }}
            </div>

            @if ($detail !== '')
                <div class="acc-detail">{{ $detail }}</div>
            @endif

            <div class="acc-summary">{{ $lb->ringkasan }}</div>

            @if ($lb->bukti_path)
                <div class="acc-evidence-row">
                    <a href="{{ route('logbook.bukti', $lb) }}" class="acc-evidence" target="_blank" rel="noopener">
                        <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
                        <span>{{ $lb->is_dinas_luar ? 'Lihat bukti surat tugas' : 'Lihat bukti pendukung' }}</span>
                        <i class="fa-solid fa-arrow-up-right-from-square acc-evidence-open" aria-hidden="true"></i>
                    </a>
                </div>
            @elseif ($lb->is_izin_sakit || in_array($lb->shift?->nama, ['Izin', 'Sakit'], true))
                <div class="acc-no-evidence" role="status">Belum ada dokumen pendukung.</div>
            @endif

            @if ($lb->keterangan_hc)
                <div class="acc-hc"><strong>Keterangan HC:</strong> {{ $lb->keterangan_hc }}</div>
            @endif

            @if ($lb->keterangan_pegawai)
                <div class="acc-hc"><strong>Keterangan Banding Pegawai:</strong> {{ $lb->keterangan_pegawai }}</div>
            @endif

            <div class="acc-actions" data-card-actions>
                @php($bandingFinal = in_array($lb->status_banding, [\App\Models\Logbook::BANDING_DISETUJUI, \App\Models\Logbook::BANDING_DITOLAK], true))
                @if (!$bandingFinal && $lb->status !== \App\Models\Logbook::STATUS_DISETUJUI)
                    <form method="POST" action="{{ route('admin.logbook.setujui', $lb) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="acc-btn acc-btn--ok">Setujui</button>
                    </form>
                @endif

                @if (!$bandingFinal && $lb->status !== \App\Models\Logbook::STATUS_DITOLAK)
                    <details class="acc-tolak">
                        <summary class="acc-btn acc-btn--no">Tolak</summary>

                        <form method="POST" action="{{ route('admin.logbook.tolak', $lb) }}">
                            @csrf
                            @method('PATCH')
                            @unless ($lb->is_cuti)
                                <label class="lb-label">Alasan penolakan</label>
                                <textarea name="keterangan_hc" class="lb-textarea" required maxlength="500"
                                          placeholder="Tuliskan alasan penolakan..."></textarea>
                            @endunless
                            <button type="submit" class="acc-btn acc-btn--no">Kirim penolakan</button>
                        </form>
                    </details>
                @endif
                @if ($bandingFinal)
                    <span class="acc-chip">Banding {{ strtolower($lb->status_banding) }}</span>
                @endif
            </div>
            <div class="acc-bulk-hint" hidden>Gunakan tombol di bawah untuk memproses pilihan.</div>

        </div>
    @empty
        <div class="lb-card">
            <div class="acc-empty">
                @if ($status === \App\Models\Logbook::STATUS_MENUNGGU && !request()->filled('jenis') && !request()->filled('dari') && !request()->filled('sampai'))
                    <i class="fa-solid fa-circle-check acc-empty-icon" aria-hidden="true"></i>
                    <strong>Semua logbook menunggu sudah diproses.</strong>
                    <span>Daftar menunggu telah diperbarui. Buka tab Disetujui atau Ditolak untuk melihat hasilnya.</span>
                @else
                    Tidak ada entri untuk pegawai ini dengan filter yang dipilih.
                @endif
                <br>
                <a href="{{ route('admin.logbook.index', request()->except('page')) }}" class="acc-back">
                    &larr; Kembali ke daftar pegawai
                </a>
            </div>
        </div>
    @endforelse

    <div class="acc-bulk-bar" id="bulk-action-bar" hidden>
        <strong><span id="bulk-selected-count">0</span> dipilih</strong>
        <label><input type="checkbox" id="select-all-bar"> Pilih semua (<span id="selectable-count">0</span>)</label>
        <button type="button" id="bulk-cancel" class="acc-btn acc-btn--ghost">Batal</button>
        <button type="submit" form="bulk-approve-form" class="acc-btn acc-btn--ok">Setujui</button>
        <button type="button" id="bulk-reject-open" class="acc-btn acc-btn--no">Tolak</button>
    </div>
    <dialog id="bulk-reject-dialog" class="acc-bulk-dialog">
        <form method="POST" action="{{ route('admin.logbook.tolak-terpilih', $user) }}" id="bulk-reject-form">
            @csrf
            <h2>Tolak <span id="bulk-reject-count">0</span> logbook?</h2>
            <p id="bulk-reject-description">Konfirmasi penolakan untuk semua logbook yang dipilih.</p>
            <div id="bulk-reject-reason-wrap">
                <label class="lb-label" for="bulk-reject-reason">Alasan penolakan</label>
                <textarea id="bulk-reject-reason" name="keterangan_hc" class="lb-textarea" required maxlength="500" placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
            <div id="bulk-reject-ids"></div>
            <div class="acc-actions"><button type="button" id="bulk-reject-cancel" class="acc-btn acc-btn--ghost">Batal</button><button type="submit" class="acc-btn acc-btn--no">Ya, tolak</button></div>
        </form>
    </dialog>

    <div class="acc-total">Menampilkan {{ $logbooks->firstItem() ?? 0 }}–{{ $logbooks->lastItem() ?? 0 }} dari {{ $logbooks->total() }} entri</div>

    {{-- Halaman sebelumnya / berikutnya --}}
    @if ($logbooks->hasPages())
        <div class="acc-pager">
            @if ($logbooks->onFirstPage())
                <span class="is-off">&laquo; Sebelumnya</span>
            @else
                <a href="{{ $logbooks->previousPageUrl() }}">&laquo; Sebelumnya</a>
            @endif

            @if ($logbooks->hasMorePages())
                <a href="{{ $logbooks->nextPageUrl() }}">Berikutnya &raquo;</a>
            @else
                <span class="is-off">Berikutnya &raquo;</span>
            @endif
        </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('acc-review-filter');
    if (form) ['jenis', 'dari', 'sampai'].forEach((name) => form.querySelector(`[name="${name}"]`)?.addEventListener('change', () => form.requestSubmit()));
    const choices = [...document.querySelectorAll('[data-logbook-choice]')];
    const bar = document.getElementById('bulk-action-bar');
    const selectAll = document.getElementById('select-all-logbooks');
    const selectAllBar = document.getElementById('select-all-bar');
    const selectRow = document.getElementById('bulk-select-row');
    if (!bar || !selectAll || !selectAllBar) return;
    document.getElementById('selectable-count').textContent = choices.length;
    selectRow.hidden = choices.length === 0;
    const updateBulkSelection = () => {
        const selected = choices.filter((input) => input.checked);
        bar.hidden = selected.length === 0;
        document.getElementById('bulk-selected-count').textContent = selected.length;
        document.getElementById('bulk-reject-count').textContent = selected.length;
        selectAll.checked = selected.length === choices.length && choices.length > 0;
        selectAll.indeterminate = selected.length > 0 && selected.length < choices.length;
        document.getElementById('select-all-label').textContent = selected.length === choices.length && choices.length > 0
            ? `Semua ${choices.length} dipilih`
            : selected.length > 0
                ? `${selected.length} dari ${choices.length} dipilih`
                : `Pilih semua (${choices.length})`;
        selectAllBar.checked = selectAll.checked;
        selectAllBar.indeterminate = selectAll.indeterminate;
        document.getElementById('clear-logbooks').hidden = selected.length === 0;
        choices.forEach((input) => {
            const card = input.closest('[data-review-card]');
            card.classList.toggle('is-selected', input.checked);
            card.querySelector('[data-card-actions]')?.classList.toggle('is-muted', input.checked);
            const hint = card.querySelector('.acc-bulk-hint');
            if (hint) hint.hidden = !input.checked;
        });
    };
    selectAll.addEventListener('change', (event) => {
        choices.forEach((input) => { input.checked = event.currentTarget.checked; });
        updateBulkSelection();
    });
    choices.forEach((input) => input.addEventListener('change', updateBulkSelection));
    selectAllBar.addEventListener('change', (event) => {
        choices.forEach((input) => { input.checked = event.currentTarget.checked; }); updateBulkSelection();
    });
    document.getElementById('clear-logbooks').addEventListener('click', () => { choices.forEach((input) => { input.checked = false; }); updateBulkSelection(); });
    document.getElementById('bulk-cancel').addEventListener('click', () => { choices.forEach((input) => { input.checked = false; }); updateBulkSelection(); });
    const dialog = document.getElementById('bulk-reject-dialog');
    document.getElementById('bulk-reject-open').addEventListener('click', () => {
        const holder = document.getElementById('bulk-reject-ids'); holder.replaceChildren();
        const selected = choices.filter((input) => input.checked);
        selected.forEach((input) => { const field = document.createElement('input'); field.type='hidden'; field.name='logbook_ids[]'; field.value=input.value; holder.append(field); });
        const onlyCuti = selected.length > 0 && selected.every((input) => input.dataset.isCuti === '1');
        const reasonWrap = document.getElementById('bulk-reject-reason-wrap');
        const reason = document.getElementById('bulk-reject-reason');
        reasonWrap.hidden = onlyCuti;
        reason.required = !onlyCuti;
        reason.value = '';
        document.getElementById('bulk-reject-description').textContent = onlyCuti
            ? 'Logbook cuti yang dipilih akan ditolak tanpa alasan.'
            : 'Tuliskan alasan penolakan untuk logbook yang dipilih.';
        dialog.showModal();
    });
    document.getElementById('bulk-reject-cancel').addEventListener('click', () => dialog.close());
    updateBulkSelection();
});
</script>
@endsection
