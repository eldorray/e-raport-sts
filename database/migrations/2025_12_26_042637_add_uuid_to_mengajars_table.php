<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Mengajar;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mengajars', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Populate existing records with UUIDs
        Mengajar::query()->each(function ($mengajar) {
            $mengajar->uuid = Str::uuid()->toString();
            $mengajar->save();
        });

        // Make uuid unique and not nullable after populating
        Schema::table('mengajars', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mengajars', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
