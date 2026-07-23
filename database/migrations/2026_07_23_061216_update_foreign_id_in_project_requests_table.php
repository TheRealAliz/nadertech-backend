<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $table->dropForeign(['project_service_id']);
            $table->dropColumn('project_service_id');

            $table->foreignId('project_request_type_id')
                ->after('id')
                ->constrained('project_request_types');
        });
    }

    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $table->dropForeign(['project_request_type_id']);
            $table->dropColumn('project_request_type_id');

            $table->foreignId('project_service_id')
                ->after('id')
                ->constrained('project_services');
        });
    }
};