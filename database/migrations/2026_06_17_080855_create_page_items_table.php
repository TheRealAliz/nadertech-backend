<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_items', function (Blueprint $table) {
            $table->id();
            $table->string('page', 50);
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->enum('type', ['text', 'html', 'image_path', 'json', 'number', 'boolean'])->default('text');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('page');
            $table->index('key');
            $table->unique(['page', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_items');
    }
};