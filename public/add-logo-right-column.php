<?php

/**
 * Script untuk menambahkan kolom logo_right ke school_profiles
 * Akses via browser: https://domain.com/add-logo-right-column.php
 * 
 * HAPUS FILE INI SETELAH SELESAI!
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<pre>";
echo "=== Menambahkan kolom logo_right ===\n\n";

try {
    // Cek apakah kolom sudah ada
    if (Schema::hasColumn('school_profiles', 'logo_right')) {
        echo "✅ Kolom 'logo_right' sudah ada di tabel school_profiles.\n";
    } else {
        // Tambahkan kolom logo_right
        DB::statement("ALTER TABLE school_profiles ADD COLUMN logo_right VARCHAR(255) NULL AFTER logo");
        echo "✅ Kolom 'logo_right' berhasil ditambahkan!\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n⚠️ PENTING: Hapus file add-logo-right-column.php setelah selesai!\n";
echo "</pre>";
