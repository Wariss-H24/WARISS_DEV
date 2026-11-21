<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom et slug d'une catégorie de compétences dans une langue.
 *
 * @property int $id
 * @property int $skill_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereSkillCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SkillCategoryTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('skill_category_translations')]
#[Fillable(['name', 'slug'])]
class SkillCategoryTranslation extends Model
{
    //
}
