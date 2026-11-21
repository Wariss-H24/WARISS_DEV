<?php

namespace App\Models\Communication;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Citation et fonction de l'auteur d'un témoignage dans une langue.
 *
 * @property int $id
 * @property int $testimonial_id
 * @property string $locale
 * @property string $quote
 * @property string|null $author_role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereAuthorRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereQuote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereTestimonialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestimonialTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('testimonial_translations')]
#[Fillable(['quote', 'author_role'])]
class TestimonialTranslation extends Model
{
    //
}
