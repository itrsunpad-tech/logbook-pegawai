<?php

return [
    // Unit => nama shift yang boleh dipilih pada form logbook harian.
    'units' => [
        'Reguler' => [
            'oh' => ['OH Reguler'],
            'shift' => ['Pagi Reguler', 'Siang Reguler', 'Malam Reguler', 'Middle'],
        ],
        'IT' => [
            'oh' => ['OH Reguler'],
            'shift' => [],
        ],
        'Gizi' => [
<<<<<<< HEAD
            'oh' => ['OH Reguler'],
            'shift' => ['Pagi Gizi & Linen', 'Siang Gizi & Linen', 'Middle'],
=======
            'oh' => ['OH IT,Gizi'],
            'shift' => ['Siang Gizi & Linen'],
>>>>>>> f947faa74445f1b0e16dfc4b5361d5dde6f72cf8
        ],
        'Lab' => [
            'oh' => ['OH Reguler'],
            'shift' => [],
        ],
        'Linen' => [
            'oh' => [],
            'shift' => ['Pagi Gizi & Linen', 'Siang Gizi & Linen', 'Middle'],
        ],
        'Hemodialisa' => [
            'oh' => [],
            'shift' => ['Pagi Hemodialisa', 'Siang Hemodialisa', 'Middle'],
        ],
    ],

    // Unit lama/tidak dikenal hanya mendapat pilihan status umum.
    'default' => ['Sakit', 'Izin'],
    'common' => ['Sakit', 'Izin'],
];
