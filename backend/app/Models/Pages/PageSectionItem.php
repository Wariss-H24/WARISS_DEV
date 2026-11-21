<?php

namespace App\Models\Pages;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Contenu mis en avant dans une section (projet, service, article, compétence…).
 *
 * Simple référence : le contenu n'est jamais recopié. itemable_type est un alias de la morph map.
 *
 * @property int $id
 * @property int $page_section_id
 * @property string $itemable_type
 * @property int $itemable_id
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $itemable
 * @property-read PageSection $section
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem whereItemableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem whereItemableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem wherePageSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('page_section_items')]
#[Fillable(['page_section_id', 'itemable_type', 'itemable_id', 'position'])]
class PageSectionItem extends Model
{
    /**
     * @return BelongsTo<PageSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
