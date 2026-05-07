<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE factures
            MODIFY statut_paiement
            ENUM('PAYE', 'PARTIEL', 'NON_PAYE')
            DEFAULT 'NON_PAYE'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE factures
            MODIFY statut_paiement
            ENUM('PAYE', 'NON_PAYE')
            DEFAULT 'NON_PAYE'
        ");
    }
};