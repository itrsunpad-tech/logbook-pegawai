<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ringkasan = [
            'pegawai' => User::where('role', User::ROLE_PEGAWAI)->count(),
            'logbook_hari_ini' => Logbook::whereDate('tanggal', today())->count(),
            'logbook_bulan_ini' => Logbook::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->count(),
        ];

        return view('admin.dashboard', compact('ringkasan'));
    }
}