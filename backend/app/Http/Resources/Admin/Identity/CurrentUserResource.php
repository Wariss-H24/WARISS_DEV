<?php

namespace App\Http\Resources\Admin\Identity;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compte connecté à l'administration. can_write ne sert qu'à adapter l'interface :
 * les droits sont toujours vérifiés par le serveur.
 *
 * @mixin User
 */
class CurrentUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'abilities' => [
                'can_write' => $this->isAdmin(),
                'can_view_sensitive_data' => $this->isAdmin(),
            ],
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ];
    }
}
