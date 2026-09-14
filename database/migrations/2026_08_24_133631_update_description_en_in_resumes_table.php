<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropColumn('description_en');
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('resume_reviews', function (Blueprint $table) {
            $table->dropColumn('description_en');
            $table->text('description_en')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropColumn('description_en');
            $table->string('description_en', 300)->nullable()->after('description');
        });

        Schema::table('resume_reviews', function (Blueprint $table) {
            $table->dropColumn('description_en');
            $table->string('description_en', 300)->nullable()->after('description');
        });
    }
};
