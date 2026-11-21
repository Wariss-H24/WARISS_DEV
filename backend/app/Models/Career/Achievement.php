<?php

namespace App\Models\Career;

use App\Enums\AchievementType;
use App\Models\Media\Media;
use App\Support\Concerns\AppearsInTimeline;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Career\AchievementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Réalisation ou distinction vérifiable (domaine D9) : prix, publication, conférence, hackathon…
 * Distincte d'un projet, d'une expérience et d'une certification.
 *
 * @property int $id
 * @property AchievementType $type
 * @property Carbon $achieved_on
 * @property string|null $organization_name
 * @property string|null $url
 * @property int|null $media_id
 * @property bool $is_visible
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media|null $media
 * @property-read AchievementTranslation|null $translation
 * @property-read Collection<int, AchievementTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Career\AchievementFactory factory($count = null, $state = [])
 * @method static Builder<static>|Achievement listsTranslations(string $translationField)
 * @method static Builder<static>|Achievement newModelQuery()
 * @method static Builder<static>|Achievement newQuery()
 * @method static Builder<static>|Achievement notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Achievement orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Achievement orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Achievement orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Achievement ordered()
 * @method static Builder<static>|Achievement query()
 * @method static Builder<static>|Achievement translated()
 * @method static Builder<static>|Achievement translatedIn(?string $locale = null)
 * @method static Builder<static>|Achievement visible()
 * @method static Builder<static>|Achievement whereAchievedOn($value)
 * @method static Builder<static>|Achievement whereCreatedAt($value)
 * @method static Builder<static>|Achievement whereId($value)
 * @method static Builder<static>|Achievement whereIsFeatured($value)
 * @method static Builder<static>|Achievement whereIsVisible($value)
 * @method static Builder<static>|Achievement whereMediaId($value)
 * @method static Builder<static>|Achievement whereOrganizationName($value)
 * @method static Builder<static>|Achievement wherePosition($value)
 * @method static Builder<static>|Achievement whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Achievement whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Achievement whereType($value)
 * @method static Builder<static>|Achievement whereUpdatedAt($value)
 * @method static Builder<static>|Achievement whereUrl($value)
 * @method static Builder<static>|Achievement withTranslation(?string $locale = null)
 *
 * @property-read Collection<int, TimelineEvent> $timelineEvents
 * @property-read int|null $timeline_events_count
 *
 * @mixin \Eloquent
 */
#[Table('achievements')]
#[Fillable(['type', 'achieved_on', 'organization_name', 'url', 'media_id', 'is_visible', 'is_featured', 'position'])]
class Achievement extends Model implements TranslatableContract
{
    /** @use HasFactory<AchievementFactory> */
    use AppearsInTimeline, HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'description'];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_visible'), true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AchievementType::class,
            'achieved_on' => 'date',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
