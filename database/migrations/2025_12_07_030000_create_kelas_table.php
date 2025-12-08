<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->string('tingkat', 20);
            $table->string('jurusan', 50)->nullable();
            $table->string('jenis', 50)->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
