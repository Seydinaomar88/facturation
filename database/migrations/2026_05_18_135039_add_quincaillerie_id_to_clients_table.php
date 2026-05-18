<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuincaillerieIdToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('quincaillerie_id')->nullable()->after('telephone')->constrained('quincailleries')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['quincaillerie_id']);
            $table->dropColumn('quincaillerie_id');
        });
    }
}