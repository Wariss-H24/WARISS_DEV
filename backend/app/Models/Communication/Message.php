<?php

namespace App\Models\Communication;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Services\Service;
use Database\Factories\Communication\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Message de contact, demande de devis ou de collaboration (domaine D13).
 *
 * Seuls les champs du formulaire public sont assignables en masse. Le statut, la note interne,
 * le consentement et l'empreinte IP sont fixés par le serveur. Les messages sont supprimés
 * définitivement au-delà de la durée de conservation (wariss.retention.messages_months).
 *
 * @property int $id
 * @property MessageType $type
 * @property int|null $service_id
 * @property string $name
 * @property string $email
 * @property string|null $subject
 * @property string $body
 * @property array<string, mixed> $details
 * @property string $locale
 * @property bool $consent_given
 * @property Carbon|null $consented_at
 * @property MessageStatus $status
 * @property Carbon|null $read_at
 * @property Carbon|null $replied_at
 * @property string|null $internal_note
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Service|null $service
 *
 * @method static \Database\Factories\Communication\MessageFactory factory($count = null, $state = [])
 * @method static Builder<static>|Message newModelQuery()
 * @method static Builder<static>|Message newQuery()
 * @method static Builder<static>|Message onlyTrashed()
 * @method static Builder<static>|Message query()
 * @method static Builder<static>|Message whereBody($value)
 * @method static Builder<static>|Message whereConsentGiven($value)
 * @method static Builder<static>|Message whereConsentedAt($value)
 * @method static Builder<static>|Message whereCreatedAt($value)
 * @method static Builder<static>|Message whereDeletedAt($value)
 * @method static Builder<static>|Message whereDetails($value)
 * @method static Builder<static>|Message whereEmail($value)
 * @method static Builder<static>|Message whereId($value)
 * @method static Builder<static>|Message whereInternalNote($value)
 * @method static Builder<static>|Message whereIpHash($value)
 * @method static Builder<static>|Message whereLocale($value)
 * @method static Builder<static>|Message whereName($value)
 * @method static Builder<static>|Message whereReadAt($value)
 * @method static Builder<static>|Message whereRepliedAt($value)
 * @method static Builder<static>|Message whereServiceId($value)
 * @method static Builder<static>|Message whereStatus($value)
 * @method static Builder<static>|Message whereSubject($value)
 * @method static Builder<static>|Message whereType($value)
 * @method static Builder<static>|Message whereUpdatedAt($value)
 * @method static Builder<static>|Message withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Message withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Table('messages')]
#[Fillable(['type', 'service_id', 'name', 'email', 'subject', 'body', 'details', 'locale'])]
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory, MassPrunable, SoftDeletes;

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Marque le message comme lu, s'il était encore nouveau.
     */
    public function markAsRead(): void
    {
        if ($this->status !== MessageStatus::New) {
            return;
        }

        $this->status = MessageStatus::Read;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Messages à supprimer définitivement : plus anciens que la durée de conservation.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        $months = (int) config('wariss.retention.messages_months');

        return static::withTrashed()->where('created_at', '<', now()->subMonths($months));
    }

    /**
     * Précisions d'une demande (budget, délai…), toujours stockées comme objet JSON.
     *
     * Un tableau PHP vide serait encodé en liste « [] » et refusé par messages_details_check :
     * il est enregistré comme objet vide « {} ».
     *
     * @return Attribute<array<string, mixed>, array<string, mixed>>
     */
    protected function details(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): array => $value === null ? [] : (array) json_decode($value, true),
            set: fn (array $value): string => $value === [] ? '{}' : (string) json_encode($value),
        );
    }

    /**
     * Adresse e-mail normalisée (minuscules, sans espaces).
     *
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => mb_strtolower(trim($value)),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
            'consent_given' => 'boolean',
            'consented_at' => 'datetime',
            'status' => MessageStatus::class,
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }
}
