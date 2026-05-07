<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailFacture extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id',
        'nom_produit',
        'quantite',
        'prix_unitaire',
        'total'
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}