<?php

namespace App\Http\Controllers\Industriel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur — Consultation de l'agrément (espace industriel, lecture seule)
 *
 * L'industriel ne peut pas modifier son agrément.
 * Il consulte celui rattaché à son unité industrielle.
 */
class AgrementController extends Controller
{
    public function show(): View
    {
        $utilisateur = Auth::user();

        // Agrément le plus récent de l'unité industrielle de l'utilisateur connecté
        $agrement = DB::table('agrements')
            ->where('unite_industrielle_id', $utilisateur->unite_industrielle_id)
            ->orderByDesc('date_delivrance')
            ->first();

        $unite = $utilisateur->unite_industrielle_id
            ? DB::table('unites_industrielles')
                ->where('id', $utilisateur->unite_industrielle_id)
                ->first()
            : null;

        return view('industriel.agrement.show', compact('agrement', 'unite'));
    }
}
