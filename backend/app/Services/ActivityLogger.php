<?php

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Models\Admin\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Enregistre les actions de l'administration dans le journal d'activité (domaine D16).
 *
 * Seuls les noms des champs modifiés sont conservés, jamais leurs valeurs : aucun mot de passe,
 * jeton ni secret ne peut ainsi entrer dans le journal.
 */
class ActivityLogger
{
    /**
     * Champs jamais mentionnés, même par leur nom.
     *
     * @var list<string>
     */
    private const IGNORED_FIELDS = ['password', 'remember_token', 'updated_at', 'created_at', 'confirmation_token_hash', 'ip_hash'];

    public function __construct(private readonly Request $request) {}

    /**
     * @param  list<string>  $changedFields
     */
    public function log(ActivityEvent $event, ?Model $subject = null, array $changedFields = [], ?User $user = null, ?string $description = null): ActivityLog
    {
        $fields = array_values(array_diff($changedFields, self::IGNORED_FIELDS));
        $user ??= $this->request->user() instanceof User ? $this->request->user() : null;

        return ActivityLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $fields === [] ? [] : ['changed' => $fields],
            'ip_address' => $this->request->ip(),
            'user_agent' => Str::limit((string) $this->request->userAgent(), 500, ''),
        ]);
    }
}
