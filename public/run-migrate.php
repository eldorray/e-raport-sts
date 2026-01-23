<?php

/**
 * Script untuk menjalankan migrasi di shared hosting
 * Akses via browser: https://domain.com/run-migrate.php
 * 
 * HAPUS FILE INI SETELAH SELESAI MIGRASI!
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "=== Running Database Migrations ===\n\n";

try {
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    
    if ($exitCode === 0) {
        echo "\n✅ Migrasi berhasil dijalankan!\n";
    } else {
        echo "\n❌ Migrasi gagal dengan exit code: {$exitCode}\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n⚠️ PENTING: Hapus file run-migrate.php setelah selesai!\n";
echo "</pre>";
