<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_service_id')->constrained('project_services');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('name', 100);
            $table->string('mobile', 13);
            $table->string('email', 100)->nullable();
            $table->text('description');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index('user_id');
            $table->index('mobile');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_requests');
    }
};