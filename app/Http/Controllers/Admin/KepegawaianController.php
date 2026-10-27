<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KepegawaianController extends Controller
{
    /** Tab detail yang sudah aktif. */
        private const TAB = ['overview', 'kontak', 'data-pribadi', 'pekerjaan', 'perbankan'];

    /**
     * Daftar pegawai + pencarian + filter "punya email".
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::in(['semua', 'terdaftar', 'belum'])],
            'q'      => ['nullable', 'string', 'max:100'],
        ]);

        $status = $filter['status'] ?? 'semua';
        $kata   = $filter['q'] ?? null;

        $pegawai = User::query()
            ->when($status === 'terdaftar', fn ($q) => $q->whereNotNull('email'))
            ->when($status === 'belum', fn ($q) => $q->whereNull('email'))
            ->when($kata, fn ($q) => $q->where(function ($w) use ($kata) {
                $w->where('name', 'like', "%{$kata}%")
                    ->orWhere('email', 'like', "%{$kata}%")
                    ->orWhere('fingerprint_id', $kata);
            }))
            ->orderBy('name')
            ->simplePaginate(20)
            ->withQueryString();

        $jumlah = [
            'semua'     => User::count(),
            'terdaftar' => User::whereNotNull('email')->count(),
            'belum'     => User::whereNull('email')->count(),
        ];

        return view('admin.kepegawaian.index', compact('pegawai', 'status', 'jumlah'));
    }

    /**
     * Detail satu pegawai (tab ditentukan lewat ?tab=...).
     */
    public function show(Request $request, User $user): View
    {
        $tab = in_array($request->query('tab'), self::TAB, true)
            ? $request->query('tab')
            : 'overview';

        $user->load('profile');

        // Daftar dropdown mencakup unit baku dan unit lama yang sudah tercatat.
        $daftarUnit = User::whereNotNull('employee_unit')
            ->distinct()
            ->orderBy('employee_unit')
            ->pluck('employee_unit')
            ->merge(User::UNIT_KERJA)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.kepegawaian.show', compact('user', 'tab', 'daftarUnit'));
    }

    /**
     * Simpan tab Overview: nama, kategori, unit.
     */
    public function updateOverview(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'employee_category' => ['nullable', Rule::in(User::KATEGORI)],
            'employee_unit'     => ['nullable', 'string', 'max:100', Rule::in(
                User::query()->whereNotNull('employee_unit')->distinct()->pluck('employee_unit')
                    ->merge(User::UNIT_KERJA)->filter()->unique()->values()->all()
            )],
        ], [
            'name.required'        => 'Nama lengkap wajib diisi.',
            'employee_category.in' => 'Kategori tidak valid.',
        ]);

        // forceFill: lewat $fillable, aman karena hanya data tervalidasi dari rute admin
        $user->forceFill($data)->save();

        return redirect()
            ->route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'overview'])
            ->with('success', 'Data overview berhasil disimpan.');
    }

    /**
     * Simpan tab Kontak: email (untuk login Google) dan nomor HP.
     */
    public function updateKontak(Request $request, User $user): RedirectResponse
    {
        // Rapikan email: huruf kecil, dan kosong dijadikan NULL (bukan string kosong,
        // karena kolom email unik dan dua string kosong akan bentrok)
        $email = Str::lower(trim((string) $request->input('email')));
        $request->merge(['email' => $email !== '' ? $email : null]);

        $data = $request->validate([
            'email' => [
                'nullable', 'email:rfc', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'email.email'  => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai pegawai lain.',
            'phone.regex'  => 'Nomor HP hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
        ]);

        // Pengaman: admin tidak boleh mengosongkan email akunnya sendiri (bisa terkunci)
        if ($user->is($request->user()) && empty($data['email'])) {
            return back()
                ->withErrors(['email' => 'Email akunmu sendiri tidak boleh dikosongkan.'])
                ->withInput();
        }

        $user->forceFill(['email' => $data['email'] ?? null])->save();

        // Profil dibuat otomatis kalau belum ada, kalau sudah ada diperbarui
        $user->profile()->updateOrCreate([], ['phone' => $data['phone'] ?? null]);

        return redirect()
            ->route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'kontak'])
            ->with('success', 'Data kontak berhasil disimpan.');
    }
        /**
     * Simpan tab Data Pribadi.
     */
    public function updateDataPribadi(Request $request, User $user): RedirectResponse
    {
        // NIK dan NPWP: buang titik/spasi/strip, sisakan angka saja
        $request->merge([
            'nik'  => $this->angkaSaja($request->input('nik')),
            'npwp' => $this->angkaSaja($request->input('npwp')),
        ]);

        $data = $request->validate([
            'nik'                 => ['nullable', 'digits:16'],
            'tempat_lahir'        => ['nullable', 'string', 'max:100'],
            'tanggal_lahir'       => ['nullable', 'date', 'before:today'],
            'agama'               => ['nullable', Rule::in(EmployeeProfile::AGAMA)],
            'jenis_kelamin'       => ['nullable', Rule::in(EmployeeProfile::JENIS_KELAMIN)],
            'pendidikan_terakhir' => ['nullable', Rule::in(EmployeeProfile::PENDIDIKAN)],
            'status_nikah'        => ['nullable', Rule::in(EmployeeProfile::STATUS_NIKAH)],
            'jumlah_anak'         => ['nullable', 'integer', 'between:0,20'],
            'nama_ibu_kandung'    => ['nullable', 'string', 'max:150'],
            'alamat_domisili'     => ['nullable', 'string', 'max:500'],
            'alamat_ktp'          => ['nullable', 'string', 'max:500'],
            'npwp'                => ['nullable', 'digits_between:15,16'],
        ], [
            'nik.digits'           => 'NIK harus 16 digit angka.',
            'npwp.digits_between'  => 'NPWP harus 15 atau 16 digit angka.',
            'tanggal_lahir.date'   => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jumlah_anak.between'  => 'Jumlah anak harus antara 0 sampai 20.',
            'jumlah_anak.integer'  => 'Jumlah anak harus berupa angka.',
        ]);

        $user->profile()->updateOrCreate([], $data);

        return redirect()
            ->route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'data-pribadi'])
            ->with('success', 'Data pribadi berhasil disimpan.');
    }

    /**
     * Simpan tab Pekerjaan.
     */
    public function updatePekerjaan(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'tanggal_mulai_kerja' => ['nullable', 'date'],
            'jabatan'             => ['nullable', 'string', 'max:100'],
            'status_kepegawaian'  => ['nullable', Rule::in(EmployeeProfile::STATUS_KEPEGAWAIAN)],
        ], [
            'tanggal_mulai_kerja.date' => 'Tanggal mulai kerja tidak valid.',
        ]);

        $user->profile()->updateOrCreate([], $data);

        return redirect()
            ->route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'pekerjaan'])
            ->with('success', 'Data pekerjaan berhasil disimpan.');
    }

    /**
     * Simpan tab Perbankan.
     */
    public function updatePerbankan(Request $request, User $user): RedirectResponse
    {
        // Nomor rekening: angka saja, nol di depan tetap dipertahankan (disimpan sebagai teks)
        $request->merge([
            'nomor_rekening' => $this->angkaSaja($request->input('nomor_rekening')),
        ]);

        $data = $request->validate([
            'nama_bank'              => ['nullable', Rule::in(EmployeeProfile::BANK)],
            'nomor_rekening'         => ['nullable', 'digits_between:5,25'],
            'nama_pemegang_rekening' => ['nullable', 'string', 'max:150'],
        ], [
            'nomor_rekening.digits_between' => 'Nomor rekening harus 5 sampai 25 digit angka.',
        ]);

        $user->profile()->updateOrCreate([], $data);

        return redirect()
            ->route('admin.kepegawaian.show', ['user' => $user, 'tab' => 'perbankan'])
            ->with('success', 'Data perbankan berhasil disimpan.');
    }

    /**
     * Buang semua karakter selain angka. Hasil kosong dijadikan NULL.
     */
    private function angkaSaja(?string $nilai): ?string
    {
        $angka = preg_replace('/\D+/', '', (string) $nilai);

        return $angka !== '' ? $angka : null;
    }
}
