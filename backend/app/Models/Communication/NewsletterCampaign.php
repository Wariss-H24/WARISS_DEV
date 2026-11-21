<?php

namespace App\Models\Communication;

use App\Enums\CampaignStatus;
use App\Models\User;
use Database\Factories\Communication\NewsletterCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Campagne de newsletter (domaine D13), rédigée dans une seule langue
 * (exception volontaire à la règle des traductions). L'envoi est développé après la V1.
 *
 * @property int $id
 * @property string $locale
 * @property string $subject
 * @property string|null $preview_text
 * @property string $body
 * @property CampaignStatus $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property-read int|null $recipients_count
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $creator
 * @property-read Collection<int, NewsletterCampaignRecipient> $recipients
 *
 * @method static \Database\Factories\Communication\NewsletterCampaignFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign wherePreviewText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereRecipientsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaign whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('newsletter_campaigns')]
#[Fillable(['locale', 'subject', 'preview_text', 'body', 'scheduled_at'])]
class NewsletterCampaign extends Model
{
    /** @use HasFactory<NewsletterCampaignFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<NewsletterCampaignRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(NewsletterCampaignRecipient::class, 'campaign_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipients_count' => 'integer',
        ];
    }
}
