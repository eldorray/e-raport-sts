<?php

return [
    'bobot_sumatif' => (float) env('RAPOR_BOBOT_SUMATIF', 50),
    'bobot_sts' => (float) env('RAPOR_BOBOT_STS', 50),

    /*
    |--------------------------------------------------------------------------
    | Grade Boundaries
    |--------------------------------------------------------------------------
    |
    | Configure the grade boundaries for calculating student predikat.
    | Values are the minimum score required for each grade level.
    |
    */
    'grade_boundaries' => [
        'sangat_baik' => (int) env('RAPOR_GRADE_SANGAT_BAIK', 86),
        'baik' => (int) env('RAPOR_GRADE_BAIK', 76),
        'cukup' => (int) env('RAPOR_GRADE_CUKUP', 61),
    ],

    /*
    |--------------------------------------------------------------------------
    | Grade Descriptors
    |--------------------------------------------------------------------------
    |
    | Predikat labels and description templates for each grade level.
    | Use [Materi/TP] as placeholder for the actual material/learning objective.
    |
    */
    'descriptors' => [
        'sangat_baik' => [
            'predikat' => 'Sangat Baik',
            'keterangan' => 'Sangat Menguasai',
            'template' => 'Peserta didik menunjukkan penguasaan yang sangat baik dalam [Materi/TP].',
        ],
        'baik' => [
            'predikat' => 'Baik',
            'keterangan' => 'Sudah Mampu',
            'template' => 'Peserta didik menunjukkan penguasaan yang baik dalam [Materi/TP].',
        ],
        'cukup' => [
            'predikat' => 'Cukup',
            'keterangan' => 'Mulai Berkembang',
            'template' => 'Peserta didik cukup mampu dalam [Materi/TP], namun masih perlu bimbingan pada bagian tertentu.',
        ],
        'perlu_bimbingan' => [
            'predikat' => 'Perlu Bimbingan',
            'keterangan' => 'Belum Mencapai',
            'template' => 'Peserta didik memerlukan bimbingan dalam [Materi/TP].',
        ],
    ],
];
