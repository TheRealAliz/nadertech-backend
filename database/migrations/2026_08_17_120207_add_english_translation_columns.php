<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotteries', function (Blueprint $table) {
            $table->string('title_en', 300)->nullable()->after('title');
            $table->text('description_en')->nullable()->after('description');
            $table->string('location_en', 500)->nullable()->after('location');
        });

        Schema::table('page_items', function (Blueprint $table) {
            $table->text('value_en')->nullable()->after('value');
        });

        Schema::table('project_services', function (Blueprint $table) {
            $table->string('title_en', 300)->nullable()->after('title');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question_en', 500)->nullable()->after('question');
            $table->text('answer_en')->nullable()->after('answer');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('title_en', 300)->nullable()->after('title');
            $table->string('alt_en', 300)->nullable()->after('alt');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('title_en', 500)->nullable()->after('title');
            $table->longText('content_en')->nullable()->after('content');
            $table->string('meta_title_en', 500)->nullable()->after('meta_title');
            $table->text('meta_description_en')->nullable()->after('meta_description');
        });

        Schema::table('resumes', function (Blueprint $table) {
            $table->string('title_en', 300)->nullable()->after('title');
            $table->string('description_en', 300)->nullable()->after('description');
        });

        Schema::table('resume_images', function (Blueprint $table) {
            $table->string('alt_en', 300)->nullable()->after('alt');
        });

        Schema::table('resume_reviews', function (Blueprint $table) {
            $table->string('name_en', 300)->nullable()->after('name');
            $table->string('description_en', 300)->nullable()->after('description');
        });

        Schema::table('project_request_types', function (Blueprint $table) {
            $table->string('title_en', 300)->nullable()->after('title');
            $table->string('description_en', 300)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        //
    }
};
