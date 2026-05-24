<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory : UtilisateurFactory
 * Génère des utilisateurs factices pour les tests et le seeding de développement.
 *
 * @extends Factory<Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;

    /**
     * État par défaut : un déclarant actif avec e-mail vérifié.
     */
    public function definition(): array
    {
        return [
            'nom'              => fake('fr_FR')->lastName(),
            'prenom'           => fake('fr_FR')->firstName(),
            'email'            => fake()->unique()->safeEmail(),
            // Vérifié par défaut ; utiliser ->nonVerifie() pour l'état non vérifié
            'email_verifie_le' => now(),
            // Le cast "hashed" du modèle applique bcrypt automatiquement
            'mot_de_passe'     => 'password',
            'role'             => 'declarant',
            'actif'            => true,
            'derniere_connexion' => null,
            'remember_token'   => Str::random(10),
        ];
    }

    /**
     * État : e-mail non encore vérifié.
     */
    public function nonVerifie(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verifie_le' => null,
        ]);
    }

    /**
     * État : compte suspendu (actif = false).
     */
    public function suspendu(): static
    {
        return $this->state(fn (array $attributes) => [
            'actif' => false,
        ]);
    }

    /**
     * État : rôle inspecteur.
     */
    public function inspecteur(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'inspecteur',
        ]);
    }

    /**
     * État : rôle administrateur.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
