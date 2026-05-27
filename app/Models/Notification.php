<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle — Notification in-app
 *
 * Représente une notification destinée à un utilisateur industriel.
 * Pas de updated_at (les notifications sont immuables une fois créées).
 */
class Notification extends Model
{
    protected $table      = 'notifications';
    public    $timestamps = false;

    protected $fillable = [
        'utilisateur_id',
        'titre',
        'message',
        'type',
        'lu',
    ];

    protected $casts = [
        'lu'         => 'boolean',
        'created_at' => 'datetime',
    ];
}
