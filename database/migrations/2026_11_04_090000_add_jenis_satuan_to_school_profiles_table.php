<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profiles', function (Blueprint $table): void {
            $table->string('jenis_satuan')->default('madrasah')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('school_profiles', function (Blueprint $table): void {
            $table->dropColumn('jenis_satuan');
        });
    }
};
