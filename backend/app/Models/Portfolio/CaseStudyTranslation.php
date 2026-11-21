<?php

namespace App\Models\Portfolio;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre, slug et résumé d'une étude de cas dans une langue.
 *
 * @property int $id
 * @property int $case_study_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereCaseStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudyTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('case_study_translations')]
#[Fillable(['title', 'slug', 'summary'])]
class CaseStudyTranslation extends Model
{
    //
}
