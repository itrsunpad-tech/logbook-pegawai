<?php

return [
    // Jenis Jam Kerja (kolom users.work_hour_type) => jadwal yang boleh dipakai pegawai.
    // Nama jadwal harus persis sama dengan kolom `nama` di tabel shifts.
    'jenis_jam_kerja' => [
        'Office Hour' => ['OH Reguler'],
        'Shift Reguler' => ['Pagi Reguler', 'Siang Reguler', 'Malam Reguler'],
        'Shift Rajal' => ['Pagi Rajal', 'Siang Rajal'],
        'Shift Gizi & Linen' => ['Pagi Gizi & Linen', 'Siang Gizi & Linen'],
        'Shift Hemodialisa' => ['Pagi Hemodialisa', 'Siang Hemodialisa'],
        'NA dan Ambulans' => ['Pagi NA dan Ambulans', 'Malam NA dan Ambulans'],
        'Middle' => ['Middle'],
    ],

    // Tap masuk sampai sekian menit SEBELUM jam mulai shift tetap dihitung shift tersebut.
    'toleransi_menit' => 60,

    // Status umum yang selalu tersedia.
    'common' => ['Sakit', 'Izin'],
];