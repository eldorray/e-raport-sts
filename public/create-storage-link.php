<?php

/**
 * Script untuk membuat storage link di shared hosting
 * Akses: https://yourdomain.com/create-storage-link.php
 * HAPUS FILE INI SETELAH DIJALANKAN!
 */
$target = __DIR__.'/../storage/app/public';
$link = __DIR__.'/storage';

// Hapus jika sudah ada (folder atau link)
if (is_link($link)) {
    unlink($link);
    echo '🗑️ Symlink lama dihapus.<br>';
} elseif (is_dir($link)) {
    // Jika folder biasa, hapus isinya dulu
    $files = array_diff(scandir($link), ['.', '..']);
    foreach ($files as $file) {
        $path = "$link/$file";
        is_dir($path) ? rmdir($path) : unlink($path);
    }
    rmdir($link);
    echo '🗑️ Folder lama dihapus.<br>';
}

// Buat symbolic link
if (symlink($target, $link)) {
    echo '✅ <strong>Storage link berhasil dibuat!</strong><br>';
    echo "📁 Target: $target<br>";
    echo "🔗 Link: $link<br>";
    echo "<br><strong style='color:red;'>⚠️ HAPUS FILE INI SEKARANG!</strong>";
} else {
    echo '❌ <strong>Gagal membuat symlink.</strong><br>';
    echo 'Kemungkinan hosting tidak mendukung symlink.<br>';
    echo '<br>Alternatif: Copy manual folder <code>storage/app/public</code> ke <code>public/storage</code>';
}
