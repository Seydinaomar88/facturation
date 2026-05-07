<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //  Ajouter client
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'nom' => 'required',
            'telephone' => 'required'
        ]);

        $client = Client::create($validated);

        return response()->json([
            'message' => 'Client créé avec succès',
            'client' => $client
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Liste clients
    public function index()
    {
        return Client::all();
    }
}