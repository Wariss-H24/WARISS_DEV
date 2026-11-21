<?php

namespace App\Models\Career;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'une formation dans une langue : intitulé, domaine, lieu, description.
 *
 * @property int $id
 * @property int $education_id
 * @property string $locale
 * @property string $degree_title
 * @property string|null $field_of_study
 * @property string|null $location_label
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereDegreeTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereEducationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereFieldOfStudy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereLocationLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EducationTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('education_translations')]
#[Fillable(['degree_title', 'field_of_study', 'location_label', 'description'])]
class EducationTranslation extends Model
{
    //
}
