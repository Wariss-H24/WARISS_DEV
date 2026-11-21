<?php

namespace App\Models\Portfolio;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'un projet dans une langue. search_vector est calculée par PostgreSQL (D-08)
 * et n'est jamais sérialisée.
 *
 * @property int $id
 * @property int $project_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property string $description
 * @property string|null $context
 * @property string $role
 * @property string|null $responsibilities
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $search_vector
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereResponsibilities($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereSearchVector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('project_translations')]
#[Fillable(['title', 'slug', 'summary', 'description', 'context', 'role', 'responsibilities'])]
#[Hidden(['search_vector'])]
class ProjectTranslation extends Model
{
    //
}
