<?php

declare(strict_types=1);

namespace Mush\Game\Service;

final class InMemoryTranslationService implements TranslationServiceInterface
{
    public function __construct(
        private array $translations = []
    ) {}

    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return $this->translations[$key] ?? $key;
    }
}
