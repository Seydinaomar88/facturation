<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quincaillerie extends Model
{
    protected $fillable = ['nom', 'adresse', 'telephone'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }
}