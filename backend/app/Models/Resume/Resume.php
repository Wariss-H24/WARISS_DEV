<?php

namespace App\Models\Resume;

use App\Enums\ResumeStatus;
use App\Enums\ResumeTarget;
use Database\Factories\Resume\ResumeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * CV (domaine D15), par langue et par public visé, avec un historique de versions.
 *
 * Seule la version courante d'un CV public et actif est téléchargeable. La base garantit
 * que la version courante appartient à ce CV (D-17), qu'un CV public est actif et a une version,
 * et qu'il n'y a qu'un CV public par défaut par langue.
 *
 * @property int $id
 * @property string $title
 * @property ResumeTarget $target
 * @property string $locale
 * @property ResumeStatus $status
 * @property bool $is_default
 * @property bool $is_public
 * @property int|null $current_version_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ResumeVersion|null $currentVersion
 * @property-read Collection<int, ResumeVersion> $versions
 * @property-read int|null $versions_count
 *
 * @method static Builder<static>|Resume downloadable()
 * @method static \Database\Factories\Resume\ResumeFactory factory($count = null, $state = [])
 * @method static Builder<static>|Resume newModelQuery()
 * @method static Builder<static>|Resume newQuery()
 * @method static Builder<static>|Resume query()
 * @method static Builder<static>|Resume whereCreatedAt($value)
 * @method static Builder<static>|Resume whereCurrentVersionId($value)
 * @method static Builder<static>|Resume whereId($value)
 * @method static Builder<static>|Resume whereIsDefault($value)
 * @method static Builder<static>|Resume whereIsPublic($value)
 * @method static Builder<static>|Resume whereLocale($value)
 * @method static Builder<static>|Resume whereStatus($value)
 * @method static Builder<static>|Resume whereTarget($value)
 * @method static Builder<static>|Resume whereTitle($value)
 * @method static Builder<static>|Resume whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('resumes')]
#[Fillable(['title', 'target', 'locale', 'status', 'is_default', 'is_public', 'current_version_id'])]
class Resume extends Model
{
    /** @use HasFactory<ResumeFactory> */
    use HasFactory;

    /**
     * Versions, de la plus récente à la plus ancienne.
     *
     * @return HasMany<ResumeVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class)->orderByDesc('version_number');
    }

    /**
     * @return BelongsTo<ResumeVersion, $this>
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(ResumeVersion::class, 'current_version_id');
    }

    /**
     * CV téléchargeables par le public : actifs, publics et dotés d'une version courante.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function downloadable(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_public'), true)
            ->where($this->qualifyColumn('status'), ResumeStatus::Active->value)
            ->whereNotNull($this->qualifyColumn('current_version_id'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target' => ResumeTarget::class,
            'status' => ResumeStatus::class,
            'is_default' => 'boolean',
            'is_public' => 'boolean',
        ];
    }
}
