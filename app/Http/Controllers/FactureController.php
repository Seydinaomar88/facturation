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

            //  CALCUL TOTAL 

            $total = 0;

            foreach ($request->produits as $produit) {

                $total += (
                    $produit['quantite']
                    * $produit['prix_unitaire']
                );
            }

            // SECURITE 

            if ($request->montant_paye > $total) {

                return response()->json([
                    'message' => 'Le montant payé ne peut pas dépasser le total'
                ], 422);
            }

            //ALCUL RESTE 

            $reste = $total - $request->montant_paye;

            //STATUT PAIEMENT

            if ($request->montant_paye == 0) {

                $statutPaiement = 'NON_PAYE';

            } elseif ($request->montant_paye < $total) {

                $statutPaiement = 'PARTIEL';

            } else {

                $statutPaiement = 'PAYE';
            }

            //STATUT GLOBAL

            $statut = $reste > 0
                ? 'DETTE'
                : 'SOLDE';

            //CREATION FACTURE 

            $facture = Facture::create([

                'client_id' => $request->client_id,

                'quincaillerie_id' => Auth::user()->quincaillerie_id,

                'total' => $total,

                'montant_paye' => $request->montant_paye,

                'reste_a_payer' => $reste,

                'statut' => $statut,

                'statut_paiement' => $statutPaiement,

                'statut_livraison' =>
                    $request->statut_livraison
                    ?? 'NON_LIVRE',
            ]);

            //DETAILS FACTURE 

            foreach ($request->produits as $produit) {

                DetailFacture::create([

                    'facture_id' => $facture->id,

                    'nom_produit' => $produit['nom_produit'],

                    'quantite' => $produit['quantite'],

                    'prix_unitaire' => $produit['prix_unitaire'],

                    'total' => (
                        $produit['quantite']
                        * $produit['prix_unitaire']
                    )
                ]);
            }

            DB::commit();

            return response()->json([

                'message' => 'Facture créée avec succès',

                'facture' => $facture->load(
                    'client',
                    'details'
                )

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

            ->where(
                'quincaillerie_id',
                $user->quincaillerie_id
            );

        // CLIENT
        if ($request->filled('client')) {

            $query->whereHas('client', function ($q) use ($request) {

                $q->where(
                    'nom',
                    'like',
                    '%' . $request->client . '%'
                );
            });
        }

        // STATUT PAIEMENT
        if ($request->filled('statut_paiement')) {

            $query->where(
                'statut_paiement',
                strtoupper(trim($request->statut_paiement))
            );
        }

        // STATUT LIVRAISON
        if ($request->filled('statut_livraison')) {

            $query->where(
                'statut_livraison',
                strtoupper(trim($request->statut_livraison))
            );
        }

        // DATE
        if ($request->filled('date')) {

            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        // DETTES
        if ($request->boolean('dette')) {

            $query->where(
                'reste_a_payer',
                '>',
                0
            );
        }

        return response()->json(

            $query
                ->latest()
                ->paginate(10)

        );
    }

    // PDF
    public function generatePdf(int $id)
    {
        $facture = Facture::with(
                'client',
                'details'
            )
            ->where(
                'quincaillerie_id',
                Auth::user()->quincaillerie_id
            )
            ->findOrFail($id);

        $pdf = Pdf::loadView(
            'factures.pdf',
            compact('facture')
        );

        return $pdf->download(
            'facture-' . $facture->id . '.pdf'
        );
    }

    // WHATSAPP
    public function sendWhatsApp(int $id)
    {
        $facture = Facture::with('client')

            ->where(
                'quincaillerie_id',
                Auth::user()->quincaillerie_id
            )

            ->findOrFail($id);

        $telephone = preg_replace(
            '/[^0-9]/',
            '',
            $facture->client->telephone
        );

        if (!str_starts_with($telephone, '221')) {

            $telephone = '221' . $telephone;
        }

        $message = urlencode(

            "Bonjour {$facture->client->nom}, "
            . "votre facture de "
            . number_format(
                $facture->total,
                0,
                ',',
                ' '
            )
            . " FCFA est disponible."
        );

        $url = "https://wa.me/{$telephone}?text={$message}";

        return response()->json([
            'whatsapp_url' => $url
        ]);
    }
}