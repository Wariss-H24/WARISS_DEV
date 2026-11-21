<?php

namespace App\Models\Portfolio;

use App\Enums\CaseStudySectionType;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Portfolio\CaseStudySectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Section typée d'une étude de cas (contexte, défi, solution, résultats…).
 *
 * @property int $id
 * @property int $case_study_id
 * @property CaseStudySectionType $type
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CaseStudy|null $caseStudy
 * @property-read CaseStudySectionTranslation|null $translation
 * @property-read Collection<int, CaseStudySectionTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Portfolio\CaseStudySectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereCaseStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySection withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('case_study_sections')]
#[Fillable(['case_study_id', 'type', 'position'])]
class CaseStudySection extends Model implements TranslatableContract
{
    /** @use HasFactory<CaseStudySectionFactory> */
    use HasFactory, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'body'];

    /**
     * @return BelongsTo<CaseStudy, $this>
     */
    public function caseStudy(): BelongsTo
    {
        return $this->belongsTo(CaseStudy::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CaseStudySectionType::class,
            'position' => 'integer',
        ];
    }
}
