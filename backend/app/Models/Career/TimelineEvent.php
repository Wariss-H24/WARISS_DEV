<?php

namespace App\Models\Career;

use App\Enums\TimelineEventType;
use App\Models\Media\Media;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Career\TimelineEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Événement de la chronologie (domaine D10).
 *
 * Lié à une source (expérience, formation, certification, projet, réalisation) : l'affichage
 * reprend les données de la source, les traductions ne servent que de surcharge.
 * Manuel (learning, event) : un titre traduit est obligatoire (validé à l'enregistrement par l'API).
 *
 * @property int $id
 * @property TimelineEventType $type
 * @property Carbon $event_date
 * @property Carbon|null $end_date
 * @property string|null $eventable_type
 * @property int|null $eventable_id
 * @property int|null $image_media_id
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|null $eventable
 * @property-read Media|null $image
 * @property-read TimelineEventTranslation|null $translation
 * @property-read Collection<int, TimelineEventTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|TimelineEvent chronological()
 * @method static \Database\Factories\Career\TimelineEventFactory factory($count = null, $state = [])
 * @method static Builder<static>|TimelineEvent listsTranslations(string $translationField)
 * @method static Builder<static>|TimelineEvent newModelQuery()
 * @method static Builder<static>|TimelineEvent newQuery()
 * @method static Builder<static>|TimelineEvent notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|TimelineEvent orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|TimelineEvent orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|TimelineEvent orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|TimelineEvent query()
 * @method static Builder<static>|TimelineEvent translated()
 * @method static Builder<static>|TimelineEvent translatedIn(?string $locale = null)
 * @method static Builder<static>|TimelineEvent visible()
 * @method static Builder<static>|TimelineEvent whereCreatedAt($value)
 * @method static Builder<static>|TimelineEvent whereEndDate($value)
 * @method static Builder<static>|TimelineEvent whereEventDate($value)
 * @method static Builder<static>|TimelineEvent whereEventableId($value)
 * @method static Builder<static>|TimelineEvent whereEventableType($value)
 * @method static Builder<static>|TimelineEvent whereId($value)
 * @method static Builder<static>|TimelineEvent whereImageMediaId($value)
 * @method static Builder<static>|TimelineEvent whereIsVisible($value)
 * @method static Builder<static>|TimelineEvent wherePosition($value)
 * @method static Builder<static>|TimelineEvent whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|TimelineEvent whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|TimelineEvent whereType($value)
 * @method static Builder<static>|TimelineEvent whereUpdatedAt($value)
 * @method static Builder<static>|TimelineEvent withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('timeline_events')]
#[Fillable(['type', 'event_date', 'end_date', 'eventable_type', 'eventable_id', 'image_media_id', 'is_visible', 'position'])]
class TimelineEvent extends Model implements TranslatableContract
{
    /** @use HasFactory<TimelineEventFactory> */
    use HasFactory, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'description'];

    /**
     * Donnée source de l'événement, s'il en a une.
     *
     * @return MorphTo<Model, $this>
     */
    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    /**
     * Indique si l'événement est saisi manuellement (sans donnée source).
     */
    public function isManual(): bool
    {
        return $this->eventable_type === null;
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
     * Du plus récent au plus ancien, puis selon l'ordre manuel.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function chronological(Builder $query): void
    {
        $query->orderByDesc($this->qualifyColumn('event_date'))
            ->orderBy($this->qualifyColumn('position'))
            ->orderByDesc($this->qualifyColumn('id'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TimelineEventType::class,
            'event_date' => 'date',
            'end_date' => 'date',
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
