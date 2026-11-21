<?php

namespace App\Models\Pages;

use App\Enums\PageKind;
use App\Enums\PublicationStatus;
use App\Support\Concerns\HasPublicationStatus;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Pages\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Page publique (domaine D18).
 *
 * Page système : route fixe du frontend (accueil, contact…), sans slug, jamais supprimée.
 * Page personnalisée : slug traduit, composée de sections.
 * La nature (kind) n'est pas modifiable par un formulaire : elle est fixée à la création.
 *
 * @property int $id
 * @property string $key
 * @property PageKind $kind
 * @property string $template
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, PageSection> $sections
 * @property-read int|null $sections_count
 * @property-read PageTranslation|null $translation
 * @property-read Collection<int, PageTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Pages\PageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Table('pages')]
#[Fillable(['key', 'template', 'status', 'published_at'])]
class Page extends Model implements TranslatableContract
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasPublicationStatus, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'summary'];

    /**
     * Empêche toute suppression d'une page système, même douce.
     */
    protected static function booted(): void
    {
        static::deleting(function (Page $page): void {
            if ($page->isSystem()) {
                throw new LogicException("La page système « {$page->key} » ne peut pas être supprimée.");
            }
        });
    }

    /**
     * @return HasMany<PageSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class);
    }

    /**
     * Indique si la page correspond à une route fixe du frontend.
     */
    public function isSystem(): bool
    {
        return $this->kind === PageKind::System;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => PageKind::class,
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }
}
