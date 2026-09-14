<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
            $table->uuid('visitor_id')->nullable();
            $table->ipAddress()->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('article_id');
            $table->index('user_id');
            $table->index('visitor_id');

            $table->index(['article_id', 'user_id']);
            $table->index(['article_id', 'visitor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_views');
    }
};
