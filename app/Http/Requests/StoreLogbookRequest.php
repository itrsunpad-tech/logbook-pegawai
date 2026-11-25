<?php

namespace App\Http\Requests;

use App\Http\Controllers\LogbookController;
use App\Models\Logbook;
use App\Models\Shift;
use App\Support\WorkSchedule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLogbookRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('jenis') === 'harian' && $this->boolean('is_cuti') && $this->filled('cuti_mulai')) {
            $this->merge(['tanggal' => $this->input('cuti_mulai')]);
        }
    }

    /**
     * Hanya user yang sudah login yang boleh menyimpan logbook.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi.
     * Field shift hanya wajib untuk harian, field jam hanya wajib untuk lembur / on call.
     */
    public function rules(): array
    {
        $tipeShift = $this->tipeShift();
        $dinasLuar = $this->boolean('is_dinas_luar')
            && ($tipeShift === 'kerja' || $this->dinasLuarTanpaFingerprint());
        $cuti = $this->input('jenis') === 'harian' && $this->boolean('is_cuti');
        $izinSakit = $this->input('jenis') === 'harian' && $this->boolean('is_izin_sakit');
        $perluBukti = $dinasLuar || $cuti;
        $melengkapiBuktiIzinSakit = $this->melengkapiBuktiIzinSakit();
        $tanggalMinimum = now()->startOfMonth()->subMonth()->toDateString();

        return [
            'jenis'       => ['required', Rule::in(['harian', 'lembur', 'oncall'])],
            'tanggal'     => $cuti
                ? ['required', 'date']
                : ['required', 'date', 'after_or_equal:' . $tanggalMinimum, 'before_or_equal:today'],
            'cuti_mulai'  => [$cuti ? 'required' : 'nullable', 'date'],
            'cuti_selesai' => [$cuti ? 'required' : 'nullable', 'date', 'after_or_equal:cuti_mulai'],

            // Harian
            'shift_id'    => [
                Rule::requiredIf(fn () => $this->input('jenis') === 'harian'
                    && ! $this->dinasLuarTanpaFingerprint()
                    && ! $this->boolean('is_cuti')
                    && ! $this->boolean('is_izin_sakit')),
                'nullable', 'integer', 'exists:shifts,id',
            ],

            // Lembur & On Call
            'jam_mulai'   => ['required_if:jenis,lembur,oncall', 'nullable', 'date_format:H:i'],
            'jam_selesai' => ['required_if:jenis,lembur,oncall', 'nullable', 'date_format:H:i', 'different:jam_mulai'],

            // Semua jenis
            'ringkasan'   => [
                $tipeShift === 'libur' || $melengkapiBuktiIzinSakit ? 'nullable' : 'required',
                'string', 'min:10', 'max:2000',
            ],
            'is_dinas_luar' => ['nullable', 'boolean'],
            'is_izin_sakit' => ['nullable', 'boolean'],
            'is_cuti' => ['nullable', 'boolean'],
            'bukti' => [
                $perluBukti ? 'required' : 'nullable',
                'file', 'mimes:png,jpg,jpeg,pdf', 'max:5120',
            ],
            'pernyataan'  => ['accepted'],
        ];
    }

    /** Pengguna boleh hanya mengunggah bukti untuk entri Izin/Sakit yang sudah ada. */
    private function melengkapiBuktiIzinSakit(): bool
    {
        if ($this->input('jenis') !== 'harian'
            || ! $this->boolean('is_izin_sakit')
            || ! $this->hasFile('bukti')
            || ! $this->user()
            || ! $this->filled('tanggal')) {
            return false;
        }

        return Logbook::query()
            ->where('user_id', $this->user()->id)
            ->whereDate('tanggal', $this->input('tanggal'))
            ->where('jenis', 'harian')
            ->where('is_izin_sakit', true)
            ->whereNull('bukti_path')
            ->whereNotIn('status', [Logbook::STATUS_DISETUJUI, Logbook::STATUS_DITOLAK])
            ->exists();
    }

    private function tipeShift(): ?string
    {
        if ($this->input('jenis') !== 'harian') {
            return null;
        }

        if ($this->boolean('is_izin_sakit')) {
            return 'izin_sakit';
        }

        $id = $this->input('shift_id');

        return WorkSchedule::tipe(is_numeric($id) ? Shift::find($id) : null);
    }

    /** Dinas luar pada mode jadwal otomatis tidak bergantung pada catatan fingerprint/jadwal. */
    private function dinasLuarTanpaFingerprint(): bool
    {
        return $this->input('jenis') === 'harian'
            && WorkSchedule::autoFingerprint($this->user())
            && $this->boolean('is_dinas_luar')
            && ! $this->filled('shift_id');
    }

    /**
     * Cek aturan yang butuh data di database.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Lewati jika validasi dasar sudah gagal
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $jenis = $this->input('jenis');
            if ($jenis !== 'harian' && collect(['is_dinas_luar', 'is_izin_sakit', 'is_cuti'])
                ->contains(fn ($field) => $this->boolean($field))) {
                $validator->errors()->add('jenis', 'Pilihan ini hanya tersedia untuk logbook harian.');
                return;
            }

            $pilihanKhusus = collect([
                $this->boolean('is_dinas_luar'),
                $this->boolean('is_izin_sakit'),
                $this->boolean('is_cuti'),
            ])->filter()->count();

            if ($pilihanKhusus > 1) {
                $validator->errors()->add('is_cuti', 'Pilih satu jenis pengajuan saja.');
                return;
            }

            if ($jenis === 'harian' && $this->boolean('is_cuti')) {
                $mulaiCuti = $this->input('cuti_mulai');
                $selesaiCuti = $this->input('cuti_selesai');
                if ($mulaiCuti && $selesaiCuti) {
                    $beririsan = Logbook::query()
                        ->where('user_id', $this->user()->id)
                        ->where(function ($query) use ($mulaiCuti, $selesaiCuti) {
                            $query->where(function ($harian) use ($mulaiCuti, $selesaiCuti) {
                                $harian->whereBetween('tanggal', [$mulaiCuti, $selesaiCuti])
                                    ->where(function ($bukanCutiDitolak) {
                                        $bukanCutiDitolak->whereNull('is_cuti')
                                            ->orWhere('is_cuti', false)
                                            ->orWhere(function ($cutiAktif) {
                                                $cutiAktif->where('is_cuti', true)
                                                    ->whereIn('status', [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI]);
                                            });
                                    });
                            })->orWhere(function ($cuti) use ($selesaiCuti, $mulaiCuti) {
                                    $cuti->where('is_cuti', true)
                                        ->whereIn('status', [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI])
                                        ->where('tanggal', '<=', $selesaiCuti)
                                        ->where('cuti_selesai', '>=', $mulaiCuti);
                                });
                        })
                        ->exists();

                    if ($beririsan) {
                        $validator->errors()->add('cuti_mulai', 'Rentang cuti bertabrakan dengan logbook yang sudah ada.');
                        return;
                    }
                }
            }

            if ($jenis === 'harian' && ! $this->boolean('is_cuti')) {
                $tanggal = $this->input('tanggal');
                $adaDalamRentangCuti = Logbook::query()
                    ->where('user_id', $this->user()->id)
                    ->where('is_cuti', true)
                    ->whereIn('status', [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI])
                    ->where('tanggal', '<=', $tanggal)
                    ->where('cuti_selesai', '>=', $tanggal)
                    ->exists();

                if ($adaDalamRentangCuti) {
                    $validator->errors()->add('tanggal', 'Tanggal ini berada dalam rentang cuti yang sudah diajukan.');
                    return;
                }
            }

                        if ($jenis === 'harian') {
                $pegawai = $this->user();
                $shift = \App\Models\Shift::find($this->input('shift_id'));
                $namaShiftDiizinkan = WorkSchedule::shiftNames($pegawai);

                if (WorkSchedule::jenis($pegawai) === null) {
                    $validator->errors()->add('shift_id', 'Jenis Jam Kerja Anda belum diatur. Hubungi HC.');
                    return;
                }

                $dinasLuarTanpaFingerprint = $this->dinasLuarTanpaFingerprint();
                $cuti = $this->boolean('is_cuti');
                $izinSakit = $this->boolean('is_izin_sakit');

                if (! $dinasLuarTanpaFingerprint && ! $cuti && ! $izinSakit && (! $shift || ! in_array($shift->nama, $namaShiftDiizinkan, true))) {
                    $validator->errors()->add('shift_id', 'Jadwal tersebut tidak tersedia untuk Jenis Jam Kerja Anda.');
                    return;
                }

                if ($izinSakit && $this->filled('shift_id')) {
                    $validator->errors()->add('shift_id', 'Izin/Sakit tidak menggunakan shift atau fingerprint.');
                    return;
                }

                if ($this->boolean('is_dinas_luar') && ! $dinasLuarTanpaFingerprint && $this->tipeShift() !== 'kerja') {
                    $validator->errors()->add('is_dinas_luar', 'Dinas Luar hanya dapat dipilih untuk shift kerja.');
                }
            }

            $tanggalCek = $this->boolean('is_cuti') ? $this->input('cuti_mulai') : $this->input('tanggal');
            $tanggalTerkunci = Logbook::query()
                ->where('user_id', $this->user()->id)
                ->whereDate('tanggal', $tanggalCek)
                ->where(function ($query) {
                    $query->whereNull('is_cuti')->orWhere('is_cuti', false);
                })
                ->whereIn('status', [Logbook::STATUS_DISETUJUI, Logbook::STATUS_DITOLAK])
                ->exists();

            if ($tanggalTerkunci) {
                $validator->errors()->add(
                    'tanggal',
                    'Tanggal ini sudah final dan tidak dapat diisi ulang.'
                );
            }

            if (in_array($jenis, ['lembur', 'oncall'], true)) {
                $mulai = $this->menitDariTengahMalam($this->input('jam_mulai'));
                $selesai = $this->menitDariTengahMalam($this->input('jam_selesai'));

                if ($selesai < $mulai) {
                    $validator->errors()->add('jam_selesai', 'Jam selesai harus setelah jam mulai.');
                } else {
                    $durasiMinimal = ($jenis === 'lembur' ? LogbookController::MIN_JAM_LEMBUR : LogbookController::MIN_JAM_ONCALL) * 60;

                    if (($selesai - $mulai) < $durasiMinimal) {
                        $minimalJam = $durasiMinimal / 60;
                        $label = $jenis === 'lembur' ? 'Lembur' : 'On call';
                        $validator->errors()->add(
                            'jam_selesai',
                            "Durasi {$label} minimal {$minimalJam} jam."
                        );
                    }
                }
            }

            if ($this->boolean('is_cuti')) {
                return;
            }

            $logbookTanggal = Logbook::query()
                ->where('user_id', $this->user()->id)
                ->whereDate('tanggal', $this->input('tanggal'));

            if ($jenis === 'harian'
                && (clone $logbookTanggal)->where('jenis', 'harian')
                    ->where(function ($query) {
                        $query->whereNull('is_cuti')
                            ->orWhere('is_cuti', false)
                            ->orWhere(function ($cutiAktif) {
                                $cutiAktif->where('is_cuti', true)
                                    ->whereIn('status', [Logbook::STATUS_MENUNGGU, Logbook::STATUS_DISETUJUI]);
                            });
                    })
                    ->exists()
                && ! $this->melengkapiBuktiIzinSakit()) {
                $validator->errors()->add('tanggal', 'Logbook harian tanggal ini sudah diisi.');
            }

            if (in_array($jenis, ['lembur', 'oncall'], true)) {
                $jenisLama = (clone $logbookTanggal)
                    ->whereIn('jenis', ['lembur', 'oncall'])
                    ->value('jenis');

                if ($jenisLama !== null) {
                    $namaJenisLama = $jenisLama === 'lembur' ? 'Lembur' : 'On Call';

                    $pesan = $jenisLama === $jenis
                        ? "{$namaJenisLama} tanggal ini sudah diisi (maksimal 1 kali)."
                        : "Sudah ada {$namaJenisLama}. Pilih Lembur atau On Call, tidak bisa keduanya.";

                    $validator->errors()->add('tanggal', $pesan);
                }
            }
        });
    }

    private function menitDariTengahMalam(string $waktu): int
    {
        [$jam, $menit] = array_map('intval', explode(':', $waktu));

        return ($jam * 60) + $menit;
    }

    public function attributes(): array
    {
        return [
            'jenis'       => 'jenis logbook',
            'tanggal'     => 'tanggal',
            'cuti_mulai'  => 'tanggal mulai cuti',
            'cuti_selesai' => 'tanggal selesai cuti',
            'shift_id'    => 'shift',
            'jam_mulai'   => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'ringkasan'   => 'ringkasan kegiatan',
            'bukti'       => 'bukti',
            'pernyataan'  => 'pernyataan',
        ];
    }

    public function messages(): array
    {
        return [
            'required'              => ':attribute wajib diisi.',
            'required_if'           => ':attribute wajib diisi.',
            'date'                  => ':attribute tidak valid.',
            'date_format'           => ':attribute harus berformat jam:menit.',
            'before_or_equal'       => ':attribute tidak boleh melebihi hari ini.',
            'after_or_equal'        => ':attribute hanya bisa diisi mulai awal bulan lalu.',
            'exists'                => ':attribute yang dipilih tidak valid.',
            'in'                    => ':attribute tidak valid.',
            'different'             => 'Jam selesai tidak boleh sama dengan jam mulai.',
            'min'                   => ':attribute minimal :min karakter.',
            'max'                   => ':attribute maksimal :max karakter.',
            'pernyataan.accepted'   => 'Pernyataan harus dicentang sebelum mengirim.',
            'bukti.mimes'           => 'Bukti harus berformat PNG, JPG, atau PDF.',
            'bukti.max'             => 'Ukuran bukti maksimal 5 MB.',
            'bukti.uploaded'        => 'Bukti gagal diunggah. Periksa ukuran file (maksimal 5 MB).',
        ];
    }
}
