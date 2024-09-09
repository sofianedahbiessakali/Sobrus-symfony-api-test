<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class BannedWordsLoader
{
    private array $bannedWords;

    public function __construct(string $filePath)
    {
        $this->bannedWords = Yaml::parseFile($filePath)['banned_words'] ?? [];
    }

    public function getBannedWords(): array
    {
        return $this->bannedWords;
    }
}