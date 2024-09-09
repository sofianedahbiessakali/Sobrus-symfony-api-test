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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use OpenApi\Attributes as OA;
use App\Service\BannedWordsLoader;

#[Route('/api/blog-articles')]
class BlogArticleController extends AbstractController
{

    private EntityManagerInterface $em;
    private BannedWordsLoader $bannedWordsLoader;

    public function __construct(EntityManagerInterface $em, BannedWordsLoader $bannedWordsLoader){
        $this->em = $em;
        $this->bannedWordsLoader = $bannedWordsLoader;
    }
    
    // POST /blog-articles to create a new blog article.
    #[Route('', name: 'create_blog_article', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new BlogArticle();
        $article->setAuthorId($data['authorId']);
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setStatus(ArticleStatus::DRAFT);  // Default to 'draft'
        $article->setSlug($data['slug']);

        $bannedWords = $this->bannedWordsLoader->getBannedWords();

        $topKeywords = $this->findTopThreeWords($data['content'], $bannedWords);
        $article->setKeywords($topKeywords);

        $validateBanned = $this->validateContentBannedWords($data['content'], $bannedWords);
        if ($validateBanned !== null) {
            return $validateBanned;
        }

        if (isset($data['coverPictureRef']) && !empty($data['coverPictureRef'])) {
            $base64String = $data['coverPictureRef'];
            $filename = $this->uploadBase64Image($base64String);
            if ($filename) {
                $article->setCoverPictureRef($filename);
            } else {
                return $this->json(['error' => 'Failed to upload image'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

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
        
        $bannedWords = $this->bannedWordsLoader->getBannedWords();
        $topKeywords = $this->findTopThreeWords($data['content'], $bannedWords);
        $article->setKeywords($topKeywords);

        $validateBanned = $this->validateContentBannedWords($data['content'], $bannedWords);
        if ($validateBanned !== null) {
            return $validateBanned;
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

    // upload base64 Image
    private function uploadBase64Image(string $base64String): ?string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif, etc.

            // Validate image type
            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                return null;
            }

            // Decode the base64 string
            $base64String = base64_decode($base64String);

            if ($base64String === false) {
                return null;
            }

            // Generate a unique filename
            $filename = uniqid() . '.' . $type;
            $filePath = $this->getParameter('cover_pictures_directory') . '/' . $filename;

            // Save the file
            try {
                file_put_contents($filePath, $base64String);
            } catch (FileException $e) {
                return null;
            }

            // Return the filename
            return $filename;
        }

        return null;
    }

    // find the 3 most frequently occurring words in a given text, excluding a list of banned words
    function findTopThreeWords(string $text, array $banned): array
    {

        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', '', $text); // Remove punctuation

        $words = explode(' ', $text);

        $wordCount = [];
        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '' || in_array($word, $banned) || strlen($word) <= 3) {
                continue;
            }

            if (!isset($wordCount[$word])) {
                $wordCount[$word] = 0;
            }
            $wordCount[$word]++;
        }

        arsort($wordCount);

        return array_slice(array_keys($wordCount), 0, 3);
    }

    // validate the content of the blog article content banned words
    function validateContentBannedWords(string $content, array $bannedWords): ?JsonResponse
    {
        foreach ($bannedWords as $bannedWord) {
            if (strpos(strtolower($content), strtolower($bannedWord)) !== false) {
                return $this->json(['error' => "Content contains a banned word: $bannedWord"], JsonResponse::HTTP_BAD_REQUEST);
            }
        }
        return null;
    }
}
