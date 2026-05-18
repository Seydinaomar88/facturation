<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DetailFacture;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FactureController extends Controller
{
    //  CREER FACTURE 
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'montant_paye' => 'required|numeric|min:0',
            'statut_livraison' => 'nullable|in:LIVRE,NON_LIVRE',
            'produits' => 'required|array|min:1',
            'produits.*.nom_produit' => 'required|string',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prix_unitaire' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // USER SAFE (IMPORTANT)
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Non authentifié'
                ], 401);
            }

            // CALCUL TOTAL
            $total = 0;
            foreach ($request->produits as $produit) {
                $total += $produit['quantite'] * $produit['prix_unitaire'];
            }

            // SECURITE
            if ($request->montant_paye > $total) {
                return response()->json([
                    'message' => 'Le montant payé ne peut pas dépasser le total'
                ], 422);
            }

            // RESTE
            $reste = $total - $request->montant_paye;

            // STATUT PAIEMENT
            if ($request->montant_paye == 0) {
                $statutPaiement = 'NON_PAYE';
            } elseif ($request->montant_paye < $total) {
                $statutPaiement = 'PARTIEL';
            } else {
                $statutPaiement = 'PAYE';
            }

            // STATUT GLOBAL
            $statut = $reste > 0 ? 'DETTE' : 'SOLDE';

            // FACTURE CREATE
            $facture = Facture::create([
                'client_id' => $request->client_id,
                'quincaillerie_id' => $user->quincaillerie_id,
                'total' => $total,
                'montant_paye' => $request->montant_paye,
                'reste_a_payer' => $reste,
                'statut' => $statut,
                'statut_paiement' => $statutPaiement,
                'statut_livraison' => $request->statut_livraison ?? 'NON_LIVRE',
            ]);

            // DETAILS
            foreach ($request->produits as $produit) {
                DetailFacture::create([
                    'facture_id' => $facture->id,
                    'nom_produit' => $produit['nom_produit'],
                    'quantite' => $produit['quantite'],
                    'prix_unitaire' => $produit['prix_unitaire'],
                    'total' => $produit['quantite'] * $produit['prix_unitaire']
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Facture créée avec succès',
                'facture' => $facture->load('client', 'details')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // LISTE FACTURES
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Facture::with('client', 'details')
            ->where('quincaillerie_id', $user->quincaillerie_id);

        // CLIENT
        if ($request->filled('client')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->client . '%');
            });
        }

        // STATUT PAIEMENT
        if ($request->filled('statut_paiement')) {
            $query->where('statut_paiement', strtoupper(trim($request->statut_paiement)));
        }

        // STATUT LIVRAISON
        if ($request->filled('statut_livraison')) {
            $query->where('statut_livraison', strtoupper(trim($request->statut_livraison)));
        }

        // DATE
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // DETTES
        if ($request->boolean('dette')) {
            $query->where('reste_a_payer', '>', 0);
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    // AFFICHER UNE FACTURE
    public function show(int $id)
    {
        try {
            $user = Auth::user();
            
            $facture = Facture::with('client', 'details')
                ->where('quincaillerie_id', $user->quincaillerie_id)
                ->findOrFail($id);

            return response()->json([
                'facture' => $facture
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Facture non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // MODIFIER FACTURE
    public function update(Request $request, int $id)
    {
        $request->validate([
            'client_id' => 'sometimes|required|exists:clients,id',
            'montant_paye' => 'sometimes|required|numeric|min:0',
            'statut_livraison' => 'nullable|in:LIVRE,NON_LIVRE',
            'produits' => 'sometimes|required|array|min:1',
            'produits.*.nom_produit' => 'required|string',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prix_unitaire' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            
            // Récupérer la facture
            $facture = Facture::where('quincaillerie_id', $user->quincaillerie_id)
                ->findOrFail($id);

            // Calculer le nouveau total si des produits sont fournis
            if ($request->has('produits')) {
                $total = 0;
                foreach ($request->produits as $produit) {
                    $total += $produit['quantite'] * $produit['prix_unitaire'];
                }
            } else {
                $total = $facture->total;
            }

            // Déterminer le montant payé
            $montant_paye = $request->has('montant_paye') ? $request->montant_paye : $facture->montant_paye;
            
            // Vérifier que le montant payé ne dépasse pas le total
            if ($montant_paye > $total) {
                return response()->json([
                    'message' => 'Le montant payé ne peut pas dépasser le total'
                ], 422);
            }

            // Calculer le reste
            $reste = $total - $montant_paye;

            // Déterminer le statut de paiement
            if ($montant_paye == 0) {
                $statutPaiement = 'NON_PAYE';
            } elseif ($montant_paye < $total) {
                $statutPaiement = 'PARTIEL';
            } else {
                $statutPaiement = 'PAYE';
            }

            // Déterminer le statut global
            $statut = $reste > 0 ? 'DETTE' : 'SOLDE';

            // Mettre à jour la facture
            $facture->update([
                'client_id' => $request->client_id ?? $facture->client_id,
                'total' => $total,
                'montant_paye' => $montant_paye,
                'reste_a_payer' => $reste,
                'statut' => $statut,
                'statut_paiement' => $statutPaiement,
                'statut_livraison' => $request->statut_livraison ?? $facture->statut_livraison,
            ]);

            // Si des nouveaux produits sont fournis, remplacer les anciens
            if ($request->has('produits')) {
                // Supprimer les anciens détails
                DetailFacture::where('facture_id', $facture->id)->delete();
                
                // Ajouter les nouveaux détails
                foreach ($request->produits as $produit) {
                    DetailFacture::create([
                        'facture_id' => $facture->id,
                        'nom_produit' => $produit['nom_produit'],
                        'quantite' => $produit['quantite'],
                        'prix_unitaire' => $produit['prix_unitaire'],
                        'total' => $produit['quantite'] * $produit['prix_unitaire']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Facture modifiée avec succès',
                'facture' => $facture->load('client', 'details')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // SUPPRIMER FACTURE
    public function destroy(int $id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            
            // Récupérer la facture
            $facture = Facture::where('quincaillerie_id', $user->quincaillerie_id)
                ->findOrFail($id);

            // Supprimer d'abord les détails de la facture
            DetailFacture::where('facture_id', $facture->id)->delete();
            
            // Supprimer la facture
            $facture->delete();

            DB::commit();

            return response()->json([
                'message' => 'Facture supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GENERER PDF
    public function generatePdf(int $id)
    {
        try {
            $facture = Facture::with('client', 'details')
                ->where('quincaillerie_id', Auth::user()->quincaillerie_id)
                ->findOrFail($id);

            $pdf = Pdf::loadView('factures.pdf', compact('facture'));
            return $pdf->download('facture-' . $facture->id . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération du PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ENVOYER PAR WHATSAPP
    public function sendWhatsApp(int $id)
    {
        try {
            $facture = Facture::with('client')
                ->where('quincaillerie_id', Auth::user()->quincaillerie_id)
                ->findOrFail($id);

            $telephone = preg_replace('/[^0-9]/', '', $facture->client->telephone);

            if (!str_starts_with($telephone, '221')) {
                $telephone = '221' . $telephone;
            }

            $message = urlencode(
                "Bonjour {$facture->client->nom}, "
                . "votre facture de "
                . number_format($facture->total, 0, ',', ' ')
                . " FCFA est disponible."
            );

            $url = "https://wa.me/{$telephone}?text={$message}";

            return response()->json([
                'whatsapp_url' => $url
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi WhatsApp',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}