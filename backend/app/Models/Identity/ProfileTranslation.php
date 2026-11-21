<?php

namespace App\Models\Identity;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes du profil dans une langue : titre, présentation courte, biographie…
 *
 * @property int $id
 * @property int $profile_id
 * @property string $locale
 * @property string $headline
 * @property string $short_bio
 * @property string $biography
 * @property string|null $location_label
 * @property string|null $availability_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereAvailabilityNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereBiography($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereHeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereLocationLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereShortBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('profile_translations')]
#[Fillable(['headline', 'short_bio', 'biography', 'location_label', 'availability_note'])]
class ProfileTranslation extends Model
{
    //
}
