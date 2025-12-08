<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable()->unique();
            $table->string('nama');
            $table->string('jenis_kelamin', 1);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('status_keluarga', 50)->nullable();
            $table->unsignedInteger('anak_ke')->nullable();
            $table->string('telpon', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('sekolah_asal', 150)->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->string('kelas_diterima', 50)->nullable();
            $table->string('nama_ayah', 150)->nullable();
            $table->string('nama_ibu', 150)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->text('alamat_orang_tua')->nullable();
            $table->string('nama_wali', 150)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->text('alamat_wali')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
