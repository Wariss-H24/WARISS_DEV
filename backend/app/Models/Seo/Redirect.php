<?php

namespace App\Models\Seo;

use App\Models\User;
use Database\Factories\Seo\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Redirection d'une ancienne URL vers une nouvelle (domaine D16).
 *
 * Créée automatiquement quand le slug d'un contenu publié change (D-06), ou à la main.
 * from_path est un chemin interne ; to_path un chemin interne ou une URL https.
 *
 * @property int $id
 * @property string $from_path
 * @property string $to_path
 * @property int $status_code
 * @property bool $is_active
 * @property int $hits
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $creator
 *
 * @method static Builder<static>|Redirect active()
 * @method static \Database\Factories\Seo\RedirectFactory factory($count = null, $state = [])
 * @method static Builder<static>|Redirect newModelQuery()
 * @method static Builder<static>|Redirect newQuery()
 * @method static Builder<static>|Redirect query()
 * @method static Builder<static>|Redirect whereCreatedAt($value)
 * @method static Builder<static>|Redirect whereCreatedBy($value)
 * @method static Builder<static>|Redirect whereFromPath($value)
 * @method static Builder<static>|Redirect whereHits($value)
 * @method static Builder<static>|Redirect whereId($value)
 * @method static Builder<static>|Redirect whereIsActive($value)
 * @method static Builder<static>|Redirect whereStatusCode($value)
 * @method static Builder<static>|Redirect whereToPath($value)
 * @method static Builder<static>|Redirect whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('redirects')]
#[Fillable(['from_path', 'to_path', 'status_code', 'is_active'])]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    /**
     * Comptabilise une utilisation de la redirection, sans réécrire le reste de la ligne.
     */
    public function recordHit(): void
    {
        $this->increment('hits');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'hits' => 'integer',
        ];
    }
}
