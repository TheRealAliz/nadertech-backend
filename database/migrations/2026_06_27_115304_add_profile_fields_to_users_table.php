<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('mobile');
            $table->string('national_code', 10)->unique()->nullable()->after('birth_date');
            $table->string('postal_code', 10)->nullable()->after('national_code');
            $table->string('province')->nullable()->after('postal_code');
            $table->text('address')->nullable()->after('province');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'national_code',
                'postal_code',
                'province',
                'address',
            ]);
        });
    }
};
