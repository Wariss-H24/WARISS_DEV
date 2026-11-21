<?php

namespace App\Models\Admin;

use App\Casts\AsJsonObject;
use App\Enums\ActivityEvent;
use App\Models\User;
use Database\Factories\Admin\ActivityLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Entrée du journal d'activité de l'administration (domaine D16).
 *
 * properties ne contient que les noms des champs modifiés : jamais de mot de passe, de jeton
 * ni de secret. Une entrée n'est jamais modifiée (pas de updated_at) et est supprimée
 * au-delà de la durée de conservation (wariss.retention.activity_logs_months).
 *
 * @property int $id
 * @property int|null $user_id
 * @property ActivityEvent $event
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $description
 * @property array<string, mixed> $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $created_at
 * @property-read Model|null $subject
 * @property-read User|null $user
 *
 * @method static \Database\Factories\Admin\ActivityLogFactory factory($count = null, $state = [])
 * @method static Builder<static>|ActivityLog newModelQuery()
 * @method static Builder<static>|ActivityLog newQuery()
 * @method static Builder<static>|ActivityLog query()
 * @method static Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static Builder<static>|ActivityLog whereDescription($value)
 * @method static Builder<static>|ActivityLog whereEvent($value)
 * @method static Builder<static>|ActivityLog whereId($value)
 * @method static Builder<static>|ActivityLog whereIpAddress($value)
 * @method static Builder<static>|ActivityLog whereProperties($value)
 * @method static Builder<static>|ActivityLog whereSubjectId($value)
 * @method static Builder<static>|ActivityLog whereSubjectType($value)
 * @method static Builder<static>|ActivityLog whereUserAgent($value)
 * @method static Builder<static>|ActivityLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Table('activity_logs')]
#[Fillable(['user_id', 'event', 'subject_type', 'subject_id', 'description', 'properties', 'ip_address', 'user_agent'])]
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory, MassPrunable;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Contenu concerné par l'action, s'il existe encore.
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Entrées à supprimer : plus anciennes que la durée de conservation.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        $months = (int) config('wariss.retention.activity_logs_months');

        return static::where('created_at', '<', now()->subMonths($months));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event' => ActivityEvent::class,
            'properties' => AsJsonObject::class,
        ];
    }
}
