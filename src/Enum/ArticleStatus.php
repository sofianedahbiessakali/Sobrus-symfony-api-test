<?php

namespace App\Enum;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case DELETED = 'deleted';
}