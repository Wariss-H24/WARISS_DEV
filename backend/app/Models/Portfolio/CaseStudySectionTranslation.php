<?php

namespace App\Models\Portfolio;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre (facultatif) et contenu d'une section d'étude de cas dans une langue.
 *
 * @property int $id
 * @property int $case_study_section_id
 * @property string $locale
 * @property string|null $title
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereCaseStudySectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudySectionTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('case_study_section_translations')]
#[Fillable(['title', 'body'])]
class CaseStudySectionTranslation extends Model
{
    //
}
