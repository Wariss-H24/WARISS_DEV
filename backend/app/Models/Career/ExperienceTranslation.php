<?php

namespace App\Models\Career;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'une expérience dans une langue : poste, lieu, résumé, description, réalisations.
 *
 * @property int $id
 * @property int $experience_id
 * @property string $locale
 * @property string $job_title
 * @property string|null $location_label
 * @property string $summary
 * @property string $description
 * @property string|null $achievements
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereAchievements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereExperienceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereLocationLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('experience_translations')]
#[Fillable(['job_title', 'location_label', 'summary', 'description', 'achievements'])]
class ExperienceTranslation extends Model
{
    //
}
