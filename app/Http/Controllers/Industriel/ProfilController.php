<?php

namespace App\Http\Controllers\Industriel;

use App\Http\Controllers\Controller;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Contrôleur industriel — Profil de l'utilisateur connecté
 *
 * Permet à l'industriel de consulter et modifier ses informations personnelles.
 * Les données de l'unité industrielle et de l'agrément sont affichées en lecture
 * seule : elles ne sont modifiables que par l'administration.
 */
class ProfilController extends Controller
{
    public function __construct(private JournalService $journal) {}

    // ── Affichage du profil ───────────────────────────────────────────────────

    public function index(): View
    {
        $utilisateur = DB::table('utilisateurs')
            ->where('id', Auth::id())
            ->first();

        abort_if(! $utilisateur, 404, 'Compte introuvable.');

        // Unité industrielle liée au compte
        $unite = $utilisateur->unite_industrielle_id
            ? DB::table('unites_industrielles')
                ->where('id', $utilisateur->unite_industrielle_id)
                ->first()
            : null;

        // Agrément le plus pertinent : priorité valide > suspendu > expirant > expiré
        $agrement = $unite
            ? DB::table('agrements')
                ->where('unite_industrielle_id', $unite->id)
                ->orderByRaw("FIELD(statut, 'valide', 'suspendu', 'expire', 'revoque')")
                ->first()
            : null;

        return view('industriel.profil.index', compact('utilisateur', 'unite', 'agrement'));
    }

    // ── Mise à jour des informations personnelles ────────────────────────────

    public function update(Request $request): RedirectResponse
    {
        $id          = Auth::id();
        $utilisateur = DB::table('utilisateurs')->where('id', $id)->first();
        abort_if(! $utilisateur, 404);

        $validated = $request->validate([
            'nom'       => ['required', 'string', 'max:100'],
            'prenom'    => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('utilisateurs')->ignore($id)],
            'telephone' => ['nullable', 'string', 'max:30'],
        ], [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required'  => 'L\'adresse e-mail est obligatoire.',
            'email.email'     => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique'    => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
        ]);

        DB::table('utilisateurs')->where('id', $id)->update([
            'nom'        => $validated['nom'],
            'prenom'     => $validated['prenom'],
            'email'      => $validated['email'],
            'telephone'  => $validated['telephone'] ?: null,
            'updated_at' => now(),
        ]);

        $this->journal->log(
            'modification_profil',
            'Mise à jour des informations personnelles (industriel)',
            $id,
            ['table' => 'utilisateurs', 'id' => $id],
        );

        return back()->with('statut', 'Vos informations ont été mises à jour.');
    }
}
