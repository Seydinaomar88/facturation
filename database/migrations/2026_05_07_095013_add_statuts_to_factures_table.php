<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {

            $table->enum('statut_paiement', [
                'PAYE',
                'NON_PAYE'
            ])->default('NON_PAYE')->after('reste_a_payer');

            $table->enum('statut_livraison', [
                'LIVRE',
                'NON_LIVRE'
            ])->default('NON_LIVRE')->after('statut_paiement');

        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {

            $table->dropColumn([
                'statut_paiement',
                'statut_livraison'
            ]);

        });
    }
};