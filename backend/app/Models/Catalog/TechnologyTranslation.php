<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Description d'une technologie dans une langue.
 *
 * @property int $id
 * @property int $technology_id
 * @property string $locale
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation whereTechnologyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('technology_translations')]
#[Fillable(['description'])]
class TechnologyTranslation extends Model
{
    //
}
