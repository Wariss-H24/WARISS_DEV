<?php

namespace App\Http\Requests\Admin\Blog;

use App\Enums\PostType;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'post_category_id' => [...$this->requiredOnCreate(), 'integer', Rule::exists('post_categories', 'id')],
            'cover_media_id' => $this->mediaRule(),
            'type' => ['sometimes', Rule::enum(PostType::class)],
            'reading_time_minutes' => ['nullable', 'integer', 'between:1,600'],
            ...$this->editorialRules(),
            ...$this->idsRules('tag_ids', 'tags'),
            ...$this->sluggedTranslationRules('post', [
                'excerpt' => ['string', 'max:1000'],
                'content' => ['string', 'max:200000'],
            ], ['excerpt', 'content']),
        ];
    }
}
