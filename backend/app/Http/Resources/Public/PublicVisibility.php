<?php

namespace App\Http\Resources\Public;

use App\Enums\TestimonialStatus;
use App\Models\Communication\Testimonial;
use Illuminate\Database\Eloquent\Model;

/**
 * Règle unique « ce contenu peut-il apparaître sur le site public ? », appliquée aux contenus
 * référencés par un autre (élément mis en avant, source d'un événement de chronologie, menu).
 */
final class PublicVisibility
{
    public static function allows(?Model $model): bool
    {
        if ($model === null) {
            return false;
        }

        if (method_exists($model, 'trashed') && $model->trashed()) {
            return false;
        }

        if (method_exists($model, 'isPubliclyVisible')) {
            return (bool) $model->isPubliclyVisible();
        }

        if ($model instanceof Testimonial) {
            return $model->status === TestimonialStatus::Approved && $model->consent_given && $model->is_visible;
        }

        foreach (['is_visible', 'is_active'] as $flag) {
            if (array_key_exists($flag, $model->getAttributes()) && ! $model->getAttribute($flag)) {
                return false;
            }
        }

        return true;
    }
}
