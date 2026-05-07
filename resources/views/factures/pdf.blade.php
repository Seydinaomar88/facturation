<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture</title>

    <style>
        body{
            font-family: DejaVu Sans;
            padding:30px;
            color:#1f2937;
            background:#fff;
        }

        .header{
            text-align:center;
            margin-bottom:30px;
            border-bottom:2px solid #e5e7eb;
            padding-bottom:15px;
        }

        .header h1{
            margin:0;
            color:#0f172a;
            font-size:26px;
        }

        .header p{
            margin:4px;
            font-size:13px;
            color:#6b7280;
        }

        .info{
            margin-bottom:20px;
            padding:10px;
            background:#f9fafb;
            border-radius:6px;
        }

        .info p{
            margin:5px 0;
            font-size:14px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
            font-size:14px;
        }

        table th{
            background:#111827;
            color:white;
            padding:10px;
        }

        table td{
            border:1px solid #e5e7eb;
            padding:10px;
            text-align:center;
        }

        .totaux{
            margin-top:25px;
            width:320px;
            float:right;
            background:#f9fafb;
            padding:15px;
            border-radius:8px;
            border:1px solid #e5e7eb;
        }

        .totaux p{
            margin:8px 0;
            font-size:14px;
        }

        .badge{
            padding:4px 8px;
            border-radius:5px;
            color:white;
            font-size:12px;
        }

        .green{ background:#16a34a; }
        .red{ background:#dc2626; }

        .footer{
            margin-top:120px;
            text-align:center;
            font-size:12px;
            color:#9ca3af;
            border-top:1px solid #e5e7eb;
            padding-top:10px;
        }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h1>QUINCAILLERIE OMAR</h1>
    <p>Dakar - Sénégal</p>
    <p>Tél : 77 123 45 67</p>
</div>

<!-- INFO CLIENT -->
<div class="info">
    <p><strong>Client :</strong> {{ $facture->client->nom }}</p>
    <p><strong>Téléphone :</strong> {{ $facture->client->telephone }}</p>
    <p><strong>Date :</strong> {{ $facture->created_at->format('d/m/Y H:i') }}</p>

    <!-- STATUT GLOBAL -->
    <p>
        <strong>Statut global :</strong>

        @if($facture->statut == 'SOLDE')
            <span class="badge green">SOLDE</span>
        @else
            <span class="badge red">DETTE</span>
        @endif
    </p>

    <!-- PAIEMENT -->
    <p>
        <strong>Paiement :</strong>

        @if($facture->statut_paiement == 'PAYE')
            <span class="badge green">PAYÉ</span>

        @elseif($facture->statut_paiement == 'PARTIEL')
            <span class="badge red">PARTIEL</span>

        @else
            <span class="badge red">NON PAYÉ</span>
        @endif
    </p>

    <!-- LIVRAISON -->
    <p>
        <strong>Livraison :</strong>

        @if($facture->statut_livraison == 'LIVRE')
            <span class="badge green">LIVRÉ</span>
        @else
            <span class="badge red">NON LIVRÉ</span>
        @endif
    </p>
</div>

<!-- TABLE PRODUITS -->
<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>Prix Unitaire</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach($facture->details as $detail)
        <tr>
            <td>{{ $detail->nom_produit }}</td>
            <td>{{ $detail->quantite }}</td>
            <td>{{ number_format($detail->prix_unitaire,0,',',' ') }} FCFA</td>
            <td>{{ number_format($detail->total,0,',',' ') }} FCFA</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- TOTAUX -->
<div class="totaux">
    <p><strong>Total :</strong> {{ number_format($facture->total,0,',',' ') }} FCFA</p>
    <p><strong>Montant payé :</strong> {{ number_format($facture->montant_paye,0,',',' ') }} FCFA</p>
    <p><strong>Reste :</strong> {{ number_format($facture->reste_a_payer,0,',',' ') }} FCFA</p>
</div>

<!-- FOOTER -->
<div class="footer">
    Merci pour votre confiance 🙏
</div>

</body>
</html>