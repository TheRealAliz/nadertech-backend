<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->string('description_en', 300)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropColumn('description_en');
        });
    }
};
