<?php

namespace App\Http\Requests;

use App\Http\Controllers\LogbookController;
use App\Models\Logbook;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLogbookRequest extends FormRequest
{
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
        return [
            'jenis'       => ['required', Rule::in(['harian', 'lembur', 'oncall'])],
            'tanggal'     => ['required', 'date', 'before_or_equal:today'],

            // Harian
            'shift_id'    => ['required_if:jenis,harian', 'nullable', 'exists:shifts,id'],
            'is_wfh'      => ['nullable', 'boolean'],

            // Lembur & On Call
            'jam_mulai'   => ['required_if:jenis,lembur,oncall', 'nullable', 'date_format:H:i'],
            'jam_selesai' => ['required_if:jenis,lembur,oncall', 'nullable', 'date_format:H:i', 'different:jam_mulai'],

            // Semua jenis
            'ringkasan'   => ['required', 'string', 'min:10', 'max:2000'],
            'pernyataan'  => ['accepted'],
        ];
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

            $sudahAda = Logbook::query()
                ->where('user_id', $this->user()->id)
                ->whereDate('tanggal', $this->input('tanggal'))
                ->where('jenis', $jenis);

            if ($jenis === 'harian' && $sudahAda->exists()) {
                $validator->errors()->add('tanggal', 'Logbook harian untuk tanggal ini sudah diisi.');
            }

            if ($jenis === 'oncall' && $sudahAda->count() >= LogbookController::MAKS_ONCALL) {
                $validator->errors()->add(
                    'tanggal',
                    'On call pada tanggal ini sudah mencapai batas ' . LogbookController::MAKS_ONCALL . ' kali per hari.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'jenis'       => 'jenis logbook',
            'tanggal'     => 'tanggal',
            'shift_id'    => 'shift',
            'jam_mulai'   => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'ringkasan'   => 'ringkasan kegiatan',
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
            'exists'                => ':attribute yang dipilih tidak valid.',
            'in'                    => ':attribute tidak valid.',
            'different'             => 'Jam selesai tidak boleh sama dengan jam mulai.',
            'min'                   => ':attribute minimal :min karakter.',
            'max'                   => ':attribute maksimal :max karakter.',
            'pernyataan.accepted'   => 'Pernyataan harus dicentang sebelum mengirim.',
        ];
    }
}