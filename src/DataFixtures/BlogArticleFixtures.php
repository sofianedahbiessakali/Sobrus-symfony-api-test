<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\BlogArticle;

class BlogArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $article = new BlogArticle();
        $article->setAuthorId(1);
        $article->setTitle('Integration Test Article');
        $article->setContent('This is the Test Content');
        $article->setSlug('integration-test-article');
        $article->setKeywords(['test']);
        $article->setCoverPictureRef('image_test.jpeg');

        $manager->persist($article);
        $manager->flush();
    }
}
