<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom et description d'une compétence dans une langue.
 *
 * @property int $id
 * @property int $skill_id
 * @property string $locale
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('skill_translations')]
#[Fillable(['name', 'description'])]
class SkillTranslation extends Model
{
    //
}
