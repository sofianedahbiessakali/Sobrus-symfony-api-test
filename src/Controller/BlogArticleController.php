<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\BlogArticle;
use App\Enum\ArticleStatus;

#[Route('/api/blog-articles')]
class BlogArticleController extends AbstractController
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    
    // POST /blog-articles to create a new blog article.
    #[Route('', name: 'create_blog_article', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new BlogArticle();
        $article->setAuthorId($data['authorId']);
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setStatus(ArticleStatus::DRAFT);  // Default to 'draft'
        $article->setSlug($data['slug']);
        $article->setCoverPictureRef($data['coverPictureRef']);
        $article->setKeywords($data['keywords'] ?? []);

        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        } 

        $this->em->persist($article);
        $this->em->flush();

        return $this->json($article, JsonResponse::HTTP_CREATED);
    }

    // GET /blog-articles to list all blog articles.
    #[Route('', name: 'list_blog_articles', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $articles = $this->em->getRepository(BlogArticle::class)->findAll();

        return $this->json($articles);
    }

    // GET /blog-articles/{id} to get details of a specific blog article.
    #[Route('/{id}', name: 'show_blog_article', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $article = $this->em->getRepository(BlogArticle::class)->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($article);
    }

    // PATCH /blog-articles/{id} to update a blog article.
    #[Route('/{id}', name: 'update_blog_article', methods: ['PATCH'])]
    public function update(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $article = $this->em->getRepository(BlogArticle::class)->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }
        if (isset($data['status'])) {
            $article->setStatus(ArticleStatus::from($data['status']));
        }
        if (isset($data['slug'])) {
            $article->setSlug($data['slug']);
        }
        if (isset($data['coverPictureRef'])) {
            $article->setCoverPictureRef($data['coverPictureRef']);
        }
        if (isset($data['keywords'])) {
            $article->setKeywords($data['keywords']);
        }

        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->em->persist($article);
        $this->em->flush();

        return $this->json($article);
    }

    // DELETE /blog-articles/{id} to soft delete a blog article.
    #[Route('/{id}', name: 'delete_blog_article', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $article = $this->em->getRepository(BlogArticle::class)->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $article->setStatus(ArticleStatus::DELETED);

        $this->em->persist($article);
        $this->em->flush();

        return $this->json(['message' => 'Article deleted successfully']);
    }
}
