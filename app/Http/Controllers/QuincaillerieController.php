<?php

namespace App\Http\Controllers;

use App\Models\Quincaillerie;
use Illuminate\Http\Request;

class QuincaillerieController extends Controller
{
    public function index()
    {
        return response()->json(
            Quincaillerie::all()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:quincailleries,nom',
            'adresse' => 'nullable|string',
            'telephone' => 'required|string'
        ]);

        $quincaillerie = Quincaillerie::create([
            'nom' => $request->nom,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
        ]);

        return response()->json([
            'message' => 'Quincaillerie créée avec succès',
            'quincaillerie' => $quincaillerie
        ], 201);
    }
}