<?php

namespace App\Models\Career;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre et description d'une certification dans une langue.
 *
 * @property int $id
 * @property int $certification_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereCertificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CertificationTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('certification_translations')]
#[Fillable(['title', 'description'])]
class CertificationTranslation extends Model
{
    //
}
