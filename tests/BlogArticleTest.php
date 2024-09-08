<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\BlogArticle;
use App\Enum\ArticleStatus;

class BlogArticleTest extends TestCase
{
    public function testSetAndGetTitle(): void
    {
        $article = new BlogArticle();
        $article->setTitle('Test Title');

        $this->assertEquals('Test Title', $article->getTitle());
    }

    public function testDefaultStatusIsDraft(): void
    {
        $article = new BlogArticle();

        $this->assertEquals(ArticleStatus::DRAFT, $article->getStatus());
    }
}
