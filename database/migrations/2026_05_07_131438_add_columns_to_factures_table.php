<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            if (!Schema::hasColumn('factures', 'statut_paiement')) {
                $table->string('statut_paiement')->default('NON_PAYE')->after('statut');
            }
            if (!Schema::hasColumn('factures', 'statut_livraison')) {
                $table->string('statut_livraison')->default('NON_LIVRE')->after('statut_paiement');
            }
            if (!Schema::hasColumn('factures', 'quincaillerie_id')) {
                $table->foreignId('quincaillerie_id')->nullable()->constrained('quincailleries')->onDelete('cascade')->after('client_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            if (Schema::hasColumn('factures', 'quincaillerie_id')) {
                $table->dropForeign(['quincaillerie_id']);
                $table->dropColumn('quincaillerie_id');
            }
            if (Schema::hasColumn('factures', 'statut_paiement')) {
                $table->dropColumn('statut_paiement');
            }
            if (Schema::hasColumn('factures', 'statut_livraison')) {
                $table->dropColumn('statut_livraison');
            }
        });
    }
};