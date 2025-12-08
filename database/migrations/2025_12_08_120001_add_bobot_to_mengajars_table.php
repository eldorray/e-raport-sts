<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mengajars', function (Blueprint $table) {
            $table->decimal('bobot_sumatif', 5, 2)->unsigned()->default(50)->after('jtm');
            $table->decimal('bobot_sts', 5, 2)->unsigned()->default(50)->after('bobot_sumatif');
        });
    }

    public function down(): void
    {
        Schema::table('mengajars', function (Blueprint $table) {
            $table->dropColumn(['bobot_sumatif', 'bobot_sts']);
        });
    }
};
