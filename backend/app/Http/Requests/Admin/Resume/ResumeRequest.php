<?php

namespace App\Http\Requests\Admin\Resume;

use App\Enums\ResumeStatus;
use App\Enums\ResumeTarget;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

/**
 * CV (D-15). Un CV public doit être actif et avoir une version courante ; le CV par défaut
 * est public. La version courante appartient forcément à ce CV.
 */
class ResumeRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'target' => ['sometimes', Rule::enum(ResumeTarget::class)],
            'locale' => $this->localeRule(),
            'status' => ['sometimes', Rule::enum(ResumeStatus::class)],
            'is_public' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
            'current_version_id' => $this->isCreating()
                ? ['prohibited']
                : ['nullable', 'integer', Rule::exists('resume_versions', 'id')->where('resume_id', $this->routeId())],
        ];
    }
}
