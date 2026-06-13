<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedInteger('capacity')->nullable(); // null یعنی نامحدود
            $table->unsignedInteger('winner_count')->default(1);

            $table->enum('status', ['draft', 'active', 'closed', 'drawn'])->default('draft');

            $table->timestamp('drawn_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};

