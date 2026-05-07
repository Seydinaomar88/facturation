<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'quincaillerie_id',
        'total',
        'montant_paye',
        'reste_a_payer',
        'statut',
        'statut_paiement',
        'statut_livraison'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(DetailFacture::class);
    }
}
