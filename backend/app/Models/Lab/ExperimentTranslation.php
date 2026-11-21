<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'une expérimentation dans une langue. search_vector est calculée par PostgreSQL (D-08)
 * et n'est jamais sérialisée.
 *
 * @property int $id
 * @property int $experiment_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property string $description
 * @property string|null $objective
 * @property string|null $technical_notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $search_vector
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereExperimentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereObjective($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereSearchVector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereTechnicalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('experiment_translations')]
#[Fillable(['title', 'slug', 'summary', 'description', 'objective', 'technical_notes'])]
#[Hidden(['search_vector'])]
class ExperimentTranslation extends Model
{
    //
}
