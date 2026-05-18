<?php

namespace App\Http\Controllers;

use App\Models\Quincaillerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuincaillerieController extends Controller
{
    // Liste toutes les quincailleries
    public function index()
    {
        $quincailleries = Quincaillerie::orderBy('nom', 'asc')->get();
        
        return response()->json($quincailleries);
    }

    // Afficher une quincaillerie spécifique
    public function show(int $id)
    {
        try {
            $quincaillerie = Quincaillerie::findOrFail($id);
            
            return response()->json($quincaillerie);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Quincaillerie non trouvée'
            ], 404);
        }
    }

    // Créer une quincaillerie
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|unique:quincailleries,nom',
                'adresse' => 'nullable|string',
                'telephone' => 'required|string',
                'email' => 'nullable|email|unique:quincailleries,email'
            ]);

            $quincaillerie = Quincaillerie::create([
                'nom' => $request->nom,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
            ]);

            return response()->json([
                'message' => 'Quincaillerie créée avec succès',
                'quincaillerie' => $quincaillerie
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Modifier une quincaillerie
    public function update(Request $request, int $id)
    {
        try {
            $quincaillerie = Quincaillerie::findOrFail($id);
            
            $request->validate([
                'nom' => 'sometimes|required|string|unique:quincailleries,nom,' . $id,
                'adresse' => 'nullable|string',
                'telephone' => 'sometimes|required|string',
                'email' => 'nullable|email|unique:quincailleries,email,' . $id
            ]);
            
            $quincaillerie->update($request->all());
            
            return response()->json([
                'message' => 'Quincaillerie modifiée avec succès',
                'quincaillerie' => $quincaillerie
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Supprimer une quincaillerie
    public function destroy(int $id)
    {
        try {
            $quincaillerie = Quincaillerie::findOrFail($id);
            
            // Vérifier si la quincaillerie a des utilisateurs
            if ($quincaillerie->users()->count() > 0) {
                return response()->json([
                    'message' => 'Impossible de supprimer cette quincaillerie car elle a des utilisateurs associés'
                ], 400);
            }
            
            // Vérifier si la quincaillerie a des factures
            if ($quincaillerie->factures()->count() > 0) {
                return response()->json([
                    'message' => 'Impossible de supprimer cette quincaillerie car elle a des factures associées'
                ], 400);
            }
            
            $quincaillerie->delete();
            
            return response()->json([
                'message' => 'Quincaillerie supprimée avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}