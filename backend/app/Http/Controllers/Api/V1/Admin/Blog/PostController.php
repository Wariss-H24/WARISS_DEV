<?php

namespace App\Http\Controllers\Api\V1\Admin\Blog;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Blog\PostRequest;
use App\Models\Blog\Post;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Articles du blog (D12). L'auteur est toujours le compte connecté.
 */
class PostController extends AdminCrudController
{
    protected function model(): string
    {
        return Post::class;
    }

    protected function formRequest(): string
    {
        return PostRequest::class;
    }

    protected function relations(): array
    {
        return ['tag_ids' => 'tags'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('post_category_id'),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('is_featured'),
            AllowedFilter::trashed(),
            AllowedFilter::scope('search'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'published_at', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }

    /**
     * L'auteur est toujours le compte connecté ; le temps de lecture est estimé depuis le contenu
     * (200 mots par minute) s'il n'est pas fourni.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        if ($isNew) {
            $model->setAttribute('author_id', request()->user()?->getKey());
        }

        $content = $data['translations']['fr']['content'] ?? null;

        if (! array_key_exists('reading_time_minutes', $data) && is_string($content)) {
            $model->setAttribute('reading_time_minutes', max(1, (int) ceil(str_word_count(strip_tags($content)) / 200)));
        }
    }
}
