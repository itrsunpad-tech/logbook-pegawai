<?php

return [
    // Unit => nama shift yang boleh dipilih pada form logbook harian.
    'units' => [
        'Reguler' => [
            'oh' => ['OH Reguler'],
            'shift' => ['Pagi Reguler', 'Siang Reguler', 'Malam Reguler'],
        ],
        'IT' => [
            'oh' => ['OH IT,Gizi'],
            'shift' => [],
        ],
        'Gizi' => [
            'oh' => ['OH IT,Gizi'],
            'shift' => ['Pagi Gizi & Linen', 'Siang Gizi & Linen'],
        ],
        'Lab' => [
            'oh' => ['OH Lab'],
            'shift' => [],
        ],
        'Linen' => [
            'oh' => [],
            'shift' => ['Pagi Gizi & Linen', 'Siang Gizi & Linen'],
        ],
        'Hemodialisa' => [
            'oh' => [],
            'shift' => ['Pagi Hemodialisa', 'Siang Hemodialisa'],
        ],
    ],

    // Unit lama/tidak dikenal hanya mendapat pilihan status umum.
    'default' => ['LIBUR', 'Sakit', 'Izin'],
    'common' => ['LIBUR', 'Sakit', 'Izin'],
];
