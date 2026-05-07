<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'quincaillerie_id')) {
                $table->foreignId('quincaillerie_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'quincaillerie_id')) {
                $table->dropForeign(['quincaillerie_id']);
                $table->dropColumn('quincaillerie_id');
            }
        });
    }
};