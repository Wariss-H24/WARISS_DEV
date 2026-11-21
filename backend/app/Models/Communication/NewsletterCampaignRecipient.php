<?php

namespace App\Models\Communication;

use App\Enums\RecipientStatus;
use Database\Factories\Communication\NewsletterCampaignRecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Envoi d'une campagne à un abonné (domaine D13) : un seul envoi par couple campagne / abonné.
 *
 * @property int $id
 * @property int $campaign_id
 * @property int $subscriber_id
 * @property RecipientStatus $status
 * @property Carbon|null $sent_at
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read NewsletterCampaign $campaign
 * @property-read NewsletterSubscriber $subscriber
 *
 * @method static \Database\Factories\Communication\NewsletterCampaignRecipientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereSubscriberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsletterCampaignRecipient whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('newsletter_campaign_recipients')]
#[Fillable(['campaign_id', 'subscriber_id', 'status', 'sent_at', 'error'])]
class NewsletterCampaignRecipient extends Model
{
    /** @use HasFactory<NewsletterCampaignRecipientFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<NewsletterCampaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'campaign_id');
    }

    /**
     * @return BelongsTo<NewsletterSubscriber, $this>
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'subscriber_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RecipientStatus::class,
            'sent_at' => 'datetime',
        ];
    }
}
