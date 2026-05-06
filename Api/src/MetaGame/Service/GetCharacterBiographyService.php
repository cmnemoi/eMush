<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Game\Service\TranslationServiceInterface;

final readonly class GetCharacterBiographyService
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function execute(string $characterName, string $language): array
    {
        return [
            'details' => [
                'fullName' => $this->translateBiographyElement($characterName, 'fullname', $language),
                'age' => $this->translateBiographyElement($characterName, 'age', $language),
                'employment' => $this->translateBiographyElement($characterName, 'employment', $language),
                'abstract' => $this->translateBiographyElement($characterName, 'bioAbstract', $language),
                'song' => $this->getSong($characterName, $language),
            ],
            'biography' => $this->parseBiography($this->translateBiographyElement($characterName, 'biography', $language)),
        ];
    }

    private function translateBiographyElement(string $characterName, string $key, string $language): string
    {
        return $this->translationService->translate(
            key: "{$characterName}.{$key}",
            parameters: [],
            domain: 'characters',
            language: $language,
        );
    }

    private function getSong(string $characterName, string $language): string
    {
        $songName = $this->translateBiographyElement($characterName, 'song_name', $language);
        $songArtist = $this->translateBiographyElement($characterName, 'song_artist', $language);

        return $this->translationService->translate(
            key: 'biography.song',
            parameters: ['song_name' => $songName, 'song_artist' => $songArtist],
            domain: 'characters',
            language: $language,
        );
    }

    private function parseBiography(string $biography): array
    {
        $structuredBiography = [];

        foreach (explode("\n", $biography) as $line) {
            $entry = $this->parseBiographyLine($line);
            if ($entry !== null) {
                $structuredBiography[] = $entry;
            }
        }

        return $structuredBiography;
    }

    private function parseBiographyLine(string $line): ?array
    {
        $line = trim($line);
        if ($line === '') {
            return null;
        }

        $line = $this->removeLeadingDash($line);

        return $this->extractDateAndEntry($line);
    }

    private function removeLeadingDash(string $line): string
    {
        if (str_starts_with($line, '-')) {
            return ltrim(substr($line, 1));
        }

        return $line;
    }

    private function extractDateAndEntry(string $line): ?array
    {
        $parts = explode(':', $line, 2);
        if (\count($parts) !== 2) {
            return null;
        }

        return [
            'date' => trim($parts[0]),
            'entry' => trim($parts[1]),
        ];
    }
}
