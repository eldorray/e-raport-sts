<?php

use App\Services\GradeDescriptorService;

beforeEach(function () {
    // Pastikan bobot default konsisten untuk test
    config()->set('rapor.bobot_sumatif', 50);
    config()->set('rapor.bobot_sts', 50);
    config()->set('rapor.grade_boundaries', [
        'sangat_baik' => 86,
        'baik' => 76,
        'cukup' => 61,
    ]);

    $this->service = new GradeDescriptorService;
});

test('menghitung nilai rapor dengan bobot 50:50', function () {
    // (80*50 + 90*50) / 100 = 85
    expect($this->service->calculateRapor(80, 90, 50, 50))->toBe(85.0);
});

test('menghitung nilai rapor dengan bobot kustom 70:30', function () {
    // (100*70 + 50*30) / 100 = 85
    expect($this->service->calculateRapor(100, 50, 70, 30))->toBe(85.0);
});

test('mengembalikan null ketika salah satu nilai belum ada', function () {
    expect($this->service->calculateRapor(null, 90, 50, 50))->toBeNull();
    expect($this->service->calculateRapor(80, null, 50, 50))->toBeNull();
});

test('mengembalikan null ketika total bobot bukan 100', function () {
    expect($this->service->calculateRapor(80, 90, 60, 60))->toBeNull();
    expect($this->service->calculateRapor(80, 90, 30, 30))->toBeNull();
});

test('deskriptor sesuai batas nilai', function (float $nilai, string $predikat) {
    $descriptor = $this->service->getDescriptor($nilai, 'Materi Uji');

    expect($descriptor)->not->toBeNull()
        ->and($descriptor['predikat'])->toBe($predikat);
})->with([
    'sangat baik' => [95, 'Sangat Baik'],
    'baik' => [80, 'Baik'],
    'cukup' => [70, 'Cukup'],
    'perlu bimbingan' => [40, 'Perlu Bimbingan'],
]);

test('template deskriptor mengganti placeholder materi', function () {
    $descriptor = $this->service->getDescriptor(90, 'Membaca Al-Quran');

    expect($descriptor['kalimat'])->toContain('Membaca Al-Quran')
        ->not->toContain('[Materi/TP]');
});
