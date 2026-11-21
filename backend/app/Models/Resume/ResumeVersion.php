<?php

namespace App\Models\Resume;

use App\Models\Media\Media;
use App\Models\User;
use Database\Factories\Resume\ResumeVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Version d'un CV (domaine D15) : un fichier PDF de la bibliothèque de médias.
 *
 * Le fichier ne peut pas être supprimé tant que la version existe (RESTRICT). Les anciennes
 * versions restent privées : seule la version courante d'un CV public est servie.
 *
 * @property int $id
 * @property int $resume_id
 * @property int $media_id
 * @property int $version_number
 * @property string|null $change_note
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $creator
 * @property-read Media $media
 * @property-read Resume $resume
 *
 * @method static \Database\Factories\Resume\ResumeVersionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereChangeNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereResumeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResumeVersion whereVersionNumber($value)
 *
 * @mixin \Eloquent
 */
#[Table('resume_versions')]
#[Fillable(['resume_id', 'media_id', 'version_number', 'change_note'])]
class ResumeVersion extends Model
{
    /** @use HasFactory<ResumeVersionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Resume, $this>
     */
    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
        ];
    }
}
