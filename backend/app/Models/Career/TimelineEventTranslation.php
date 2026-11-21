<?php

namespace App\Models\Career;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre et description d'un événement de chronologie dans une langue (surcharge facultative
 * pour un événement lié à une source, obligatoire pour un événement manuel).
 *
 * @property int $id
 * @property int $timeline_event_id
 * @property string $locale
 * @property string|null $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereTimelineEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimelineEventTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('timeline_event_translations')]
#[Fillable(['title', 'description'])]
class TimelineEventTranslation extends Model
{
    //
}
