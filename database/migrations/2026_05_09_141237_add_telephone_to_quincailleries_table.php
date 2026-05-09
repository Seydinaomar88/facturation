<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    if (!Schema::hasColumn('quincailleries', 'telephone')) {
        Schema::table('quincailleries', function (Blueprint $table) {
            $table->string('telephone')->nullable();
        });
    }
}

public function down(): void
{
    Schema::table('quincailleries', function (Blueprint $table) {
        $table->dropColumn('telephone');
    });
}
};
