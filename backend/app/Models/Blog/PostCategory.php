<?php

namespace App\Models\Blog;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Blog\PostCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Catégorie d'articles (domaine D12). Un article appartient à une seule catégorie.
 *
 * @property int $id
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Post> $posts
 * @property-read int|null $posts_count
 * @property-read PostCategoryTranslation|null $translation
 * @property-read Collection<int, PostCategoryTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|PostCategory active()
 * @method static \Database\Factories\Blog\PostCategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|PostCategory listsTranslations(string $translationField)
 * @method static Builder<static>|PostCategory newModelQuery()
 * @method static Builder<static>|PostCategory newQuery()
 * @method static Builder<static>|PostCategory notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|PostCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|PostCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|PostCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|PostCategory ordered()
 * @method static Builder<static>|PostCategory query()
 * @method static Builder<static>|PostCategory translated()
 * @method static Builder<static>|PostCategory translatedIn(?string $locale = null)
 * @method static Builder<static>|PostCategory whereCreatedAt($value)
 * @method static Builder<static>|PostCategory whereId($value)
 * @method static Builder<static>|PostCategory whereIsActive($value)
 * @method static Builder<static>|PostCategory wherePosition($value)
 * @method static Builder<static>|PostCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|PostCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|PostCategory whereUpdatedAt($value)
 * @method static Builder<static>|PostCategory withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('post_categories')]
#[Fillable(['position', 'is_active'])]
class PostCategory extends Model implements TranslatableContract
{
    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug', 'description'];

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_active'), true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
