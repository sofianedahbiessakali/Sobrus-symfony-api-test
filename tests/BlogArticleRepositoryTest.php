<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\BlogArticle;

class BlogArticleRepositoryTest extends KernelTestCase
{
    public function testFindAllArticles(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $repository = $container->get('doctrine')->getRepository(BlogArticle::class);

        $articles = $repository->findAll();

        $this->assertIsArray($articles);
        $this->assertCount(0, $articles);  // Assuming no articles exist in test DB initially
    }

    public function testPersistAndRetrieveArticle(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Create and persist a new BlogArticle
        $article = new BlogArticle();
        $article->setAuthorId(19);
        $article->setTitle('Integration Test Article');
        $article->setContent('This is the Test Content');
        $article->setSlug('integration-test-article');
        $article->setKeywords(['test']);
        $article->setCoverPictureRef('image_test.jpeg');

        $entityManager->persist($article);
        $entityManager->flush();

        // Retrieve the article
        $retrievedArticle = $entityManager->getRepository(BlogArticle::class)->findOneBy(['slug' => 'integration-test-article']);

        $this->assertNotNull($retrievedArticle);
        $this->assertEquals('Integration Test Article', $retrievedArticle->getTitle());
    }
}