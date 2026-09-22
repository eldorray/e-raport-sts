<?php

use App\Services\KelasResolverService;

it('mengurai label rombel API menjadi tingkat dan nama rombel', function (string $label, string $tingkat, string $nama) {
    $resolver = new KelasResolverService(1);

    expect($resolver->parseRombel($label))->toBe([
        'tingkat' => $tingkat,
        'nama' => $nama,
    ]);
})->with([
    'MI tingkat 1' => ['Kelas 1 - KELAS 1A', 'I', '1A'],
    'MI tingkat 6' => ['Kelas 6 - KELAS 6B', 'VI', '6B'],
    'SMP tingkat 7' => ['Kelas VII - Kelas VII A', 'VII', 'VII A'],
    'SMP tingkat 9' => ['Kelas IX - Kelas IX C', 'IX', 'IX C'],
    'tanpa pemisah' => ['KELAS 2C', 'II', '2C'],
    'hanya tingkat' => ['Kelas 4', 'IV', '4'],
    'huruf kecil' => ['kelas 3 - kelas 3a', 'III', '3A'],
    'pemisah en dash' => ['Kelas 5 – KELAS 5A', 'V', '5A'],
]);

it('mengembalikan null untuk label rombel kosong', function (?string $label) {
    $resolver = new KelasResolverService(1);

    expect($resolver->parseRombel($label))->toBeNull();
})->with([
    'kosong' => '',
    'spasi' => '   ',
    'null' => null,
]);
