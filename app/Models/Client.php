<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'telephone'];

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }
}
