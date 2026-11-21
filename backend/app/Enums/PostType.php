<?php

namespace App\Enums;

/**
 * Type d'un article du blog (domaine D12). Valeurs reprises dans la contrainte CHECK posts_type_check.
 */
enum PostType: string
{
    case Article = 'article';
    case Tutorial = 'tutorial';
    case Guide = 'guide';
    case Feedback = 'feedback';
    case Note = 'note';
    case Tip = 'tip';
    case News = 'news';
}
