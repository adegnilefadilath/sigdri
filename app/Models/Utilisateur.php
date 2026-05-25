<?php

namespace App\Models;

use Database\Factories\UtilisateurFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle Eloquent : Utilisateur
 *
 * Représente un compte utilisateur du système SIGDRI.
 * Étend Authenticatable pour bénéficier de l'authentification Laravel native.
 *
 * @property int         $id
 * @property string      $nom
 * @property string      $prenom
 * @property string      $email
 * @property string      $mot_de_passe
 * @property string      $role           super_admin|admin|inspecteur|declarant|industriel
 * @property int|null    $unite_industrielle_id
 * @property bool        $actif
 * @property \Carbon\Carbon|null $derniere_connexion
 * @property \Carbon\Carbon|null $email_verifie_le
 */
class Utilisateur extends Authenticatable
{
    /** @use HasFactory<UtilisateurFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // Nom de la table en base (override du défaut Laravel "utilisateurs" est déjà correct,
    // mais on le déclare explicitement pour la lisibilité)
    protected $table = 'utilisateurs';

    // Colonne du mot de passe — override nécessaire car Laravel attend "password" par défaut
    protected $authPasswordName = 'mot_de_passe';

    /**
     * Colonnes autorisées à l'assignation de masse.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'role',
        'unite_industrielle_id',
        'actif',
        'derniere_connexion',
    ];

    /**
     * Colonnes masquées lors de la sérialisation (JSON/API).
     */
    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    /**
     * Transtypage automatique des attributs.
     */
    protected function casts(): array
    {
        return [
            'email_verifie_le'   => 'datetime',
            'derniere_connexion' => 'datetime',
            'actif'              => 'boolean',
            // Hashage automatique à l'écriture via bcrypt
            'mot_de_passe'       => 'hashed',
        ];
    }

    // ─── Surcharges du contrat Authenticatable ────────────────────────────────

    /**
     * Retourne le nom de la colonne utilisée comme identifiant de connexion.
     * Nécessaire pour que Laravel Auth utilise "mot_de_passe" au lieu de "password".
     */
    public function getAuthPasswordName(): string
    {
        return 'mot_de_passe';
    }

    // ─── Accesseurs pratiques ─────────────────────────────────────────────────

    /**
     * Retourne le prénom suivi du nom (ex : "Jean DUPONT").
     */
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . mb_strtoupper($this->nom);
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    /**
     * Déclarations soumises par cet utilisateur (rôle déclarant).
     */
    public function declarations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Declaration::class, 'declarant_id');
    }

    /**
     * Déclarations validées ou rejetées par cet utilisateur (rôle inspecteur).
     */
    public function declarationsValidees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Declaration::class, 'validateur_id');
    }

    /**
     * Rapports générés par cet utilisateur.
     */
    public function rapports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rapport::class, 'genere_par');
    }

    /**
     * Entrées du journal d'audit liées à cet utilisateur.
     */
    public function journaux(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Journal::class, 'utilisateur_id');
    }

    /**
     * Unité industrielle gérée par ce compte (rôle industriel uniquement).
     */
    public function uniteIndustrielle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\stdClass::class, 'unite_industrielle_id');
        // Remplacer stdClass par le modèle UniteIndustrielle quand il sera créé
    }

    // ─── Helpers de rôle ─────────────────────────────────────────────────────

    /** Vérifie si l'utilisateur est un industriel. */
    public function estIndustriel(): bool
    {
        return $this->role === 'industriel';
    }

    /** Vérifie si l'utilisateur est un administrateur (admin ou super_admin). */
    public function estAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }
}
