<?php

namespace App\Models\Career;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre et description d'une réalisation dans une langue.
 *
 * @property int $id
 * @property int $achievement_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AchievementTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('achievement_translations')]
#[Fillable(['title', 'description'])]
class AchievementTranslation extends Model
{
    //
}
