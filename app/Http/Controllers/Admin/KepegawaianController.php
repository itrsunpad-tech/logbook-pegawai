<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KepegawaianController extends Controller
{
    /** Tab detail yang sudah aktif. */
    private const TAB = ['overview', 'kontak'];

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

        // Saran isian untuk kolom unit (dari unit yang sudah pernah diisi)
        $daftarUnit = User::whereNotNull('employee_unit')
            ->distinct()
            ->orderBy('employee_unit')
            ->pluck('employee_unit');

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
            'employee_unit'     => ['nullable', 'string', 'max:100'],
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
}