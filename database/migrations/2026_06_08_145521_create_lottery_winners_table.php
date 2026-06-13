<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_winners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lottery_id')
                ->constrained('lotteries')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('position')->default(1);

            $table->timestamps();

            $table->unique(['lottery_id', 'user_id']);
            $table->unique(['lottery_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_winners');
    }
};
