<?php

namespace App\Http\Requests\Admin\Media;

use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class MediaCollectionRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'slug' => [...$this->requiredOnCreate(), ...$this->slugFormat(), Rule::unique('media_collections', 'slug')->ignore($this->routeId())],
            'description' => ['nullable', 'string', 'max:2000'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
