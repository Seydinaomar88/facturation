<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    // Ajouter client
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'telephone' => 'required|string|max:20'
            ]);

            // Ajouter automatiquement l'ID de la quincaillerie connectée
            $validated['quincaillerie_id'] = Auth::user()->quincaillerie_id;

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

    // Liste clients (filtrés par quincaillerie)
    public function index()
    {
        $user = Auth::user();
        
        $clients = Client::where('quincaillerie_id', $user->quincaillerie_id)
            ->orderBy('nom', 'asc')
            ->get();
            
        return response()->json($clients);
    }

    // Afficher un client spécifique
    public function show(int $id)
    {
        try {
            $user = Auth::user();
            
            $client = Client::where('quincaillerie_id', $user->quincaillerie_id)
                ->findOrFail($id);
                
            return response()->json($client);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Client non trouvé'
            ], 404);
        }
    }

    // Modifier un client
    public function update(Request $request, int $id)
    {
        try {
            $user = Auth::user();
            
            $client = Client::where('quincaillerie_id', $user->quincaillerie_id)
                ->findOrFail($id);
                
            $validated = $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'telephone' => 'sometimes|required|string|max:20'
            ]);
            
            $client->update($validated);
            
            return response()->json([
                'message' => 'Client modifié avec succès',
                'client' => $client
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Supprimer un client
    public function destroy(int $id)
    {
        try {
            $user = Auth::user();
            
            $client = Client::where('quincaillerie_id', $user->quincaillerie_id)
                ->findOrFail($id);
            
            // Vérifier si le client a des factures
            if ($client->factures()->count() > 0) {
                return response()->json([
                    'message' => 'Impossible de supprimer ce client car il a des factures associées'
                ], 400);
            }
                
            $client->delete();
            
            return response()->json([
                'message' => 'Client supprimé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}