<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('bobot_sumatif', 6, 2)->default(50)->after('is_active');
            $table->decimal('bobot_sts', 6, 2)->default(50)->after('bobot_sumatif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bobot_sumatif', 'bobot_sts']);
        });
    }
};
