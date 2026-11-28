<?php

namespace App\Models\Communication;

use App\Enums\SubscriberStatus;
use Database\Factories\Communication\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Abonné à la newsletter (domaine D13), avec double confirmation.
 *
 * Le jeton de confirmation n'est jamais stocké en clair : seule son empreinte SHA-256 l'est,
 * le jeton lui-même n'existe que dans l'e-mail envoyé. Suppression définitive sur demande.
 *
 * @property int $id
 * @property string $email
 * @property string $locale
 * @property SubscriberStatus $status
 * @property string|null $confirmation_token_hash
 * @property Carbon|null $confirmed_at
 * @property Carbon $consented_at
 * @property string $consent_source
 * @property Carbon|null $unsubscribed_at
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, NewsletterCampaignRecipient> $deliveries
 * @property-read int|null $deliveries_count
 *
 * @method static \Database\Factories\Communication\NewsletterSubscriberFactory factory($count = null, $state = [])
 * @method static Builder<static>|NewsletterSubscriber newModelQuery()
 * @method static Builder<static>|NewsletterSubscriber newQuery()
 * @method static Builder<static>|NewsletterSubscriber query()
 * @method static Builder<static>|NewsletterSubscriber subscribed()
 * @method static Builder<static>|NewsletterSubscriber whereConfirmationTokenHash($value)
 * @method static Builder<static>|NewsletterSubscriber whereConfirmedAt($value)
 * @method static Builder<static>|NewsletterSubscriber whereConsentSource($value)
 * @method static Builder<static>|NewsletterSubscriber whereConsentedAt($value)
 * @method static Builder<static>|NewsletterSubscriber whereCreatedAt($value)
 * @method static Builder<static>|NewsletterSubscriber whereEmail($value)
 * @method static Builder<static>|NewsletterSubscriber whereId($value)
 * @method static Builder<static>|NewsletterSubscriber whereIpHash($value)
 * @method static Builder<static>|NewsletterSubscriber whereLocale($value)
 * @method static Builder<static>|NewsletterSubscriber whereStatus($value)
 * @method static Builder<static>|NewsletterSubscriber whereUnsubscribedAt($value)
 * @method static Builder<static>|NewsletterSubscriber whereUpdatedAt($value)
 * @method static Builder<static>|NewsletterSubscriber withConfirmationToken(string $token)
 *
 * @mixin \Eloquent
 */
#[Table('newsletter_subscribers')]
#[Fillable(['email', 'locale', 'consent_source'])]
#[Hidden(['confirmation_token_hash', 'ip_hash'])]
class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    /**
     * Génère un nouveau jeton de confirmation, en stocke l'empreinte et renvoie le jeton en clair
     * (à placer dans le lien de l'e-mail, puis à oublier).
     */
    public function issueConfirmationToken(): string
    {
        $token = Str::random(64);
        $this->confirmation_token_hash = hash('sha256', $token);
        $this->save();

        return $token;
    }

    /**
     * Confirme l'abonnement et invalide le jeton.
     */
    public function confirm(): void
    {
        $this->status = SubscriberStatus::Subscribed;
        $this->confirmed_at = now();
        $this->confirmation_token_hash = null;
        $this->save();
    }

    /**
     * Désabonne l'adresse : plus aucun envoi.
     */
    public function unsubscribe(): void
    {
        $this->status = SubscriberStatus::Unsubscribed;
        $this->unsubscribed_at = now();
        $this->confirmation_token_hash = null;
        $this->save();
    }

    /**
     * Jeton du lien de désinscription présent dans chaque e-mail : signé (APP_KEY), sans table,
     * propre à l'abonné et à son adresse, valable sans limite de durée.
     */
    public function unsubscribeToken(): string
    {
        return hash_hmac('sha256', "newsletter-unsubscribe:{$this->id}:{$this->email}", (string) config('app.key'));
    }

    /**
     * URL de désinscription du site public, dans la langue de l'abonné.
     */
    public function unsubscribeUrl(): string
    {
        return config('wariss.site_url')."/{$this->locale}/newsletter/unsubscribe?id={$this->id}&token={$this->unsubscribeToken()}";
    }

    /**
     * @return HasMany<NewsletterCampaignRecipient, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterCampaignRecipient::class, 'subscriber_id');
    }

    /**
     * Abonné correspondant à un jeton de confirmation reçu en clair.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withConfirmationToken(Builder $query, string $token): void
    {
        $query->where($this->qualifyColumn('confirmation_token_hash'), hash('sha256', $token));
    }

    /**
     * Abonnés confirmés, destinataires des campagnes.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function subscribed(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), SubscriberStatus::Subscribed->value);
    }

    /**
     * Adresse e-mail normalisée (minuscules, sans espaces) : décision D-07.
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
            'status' => SubscriberStatus::class,
            'confirmed_at' => 'datetime',
            'consented_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
