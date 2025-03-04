<?php

namespace Mush\Tests\unit\RoomLog\TestDoubles;

use Mush\Game\Service\TranslationServiceInterface;

final class TranslationService implements TranslationServiceInterface
{
    private array $translations = [];

    public function setTranslation(string $key, array $parameters, string $domain, string $language, string $translation): void
    {
        $this->translations[$this->buildKey($key, $parameters, $domain, $language)] = $translation;
    }

    public function translate(string $key, array $parameters = [], ?string $domain = null, ?string $language = null): string
    {
        $builtKey = $this->buildKey($key, $parameters, $domain, $language);

        return $this->translations[$builtKey] ?? "translation_not_found_{$builtKey}";
    }

    private function buildKey(string $key, array $parameters, ?string $domain, ?string $language): string
    {
        $parametersString = json_encode($parameters);

        return "{$key}_{$parametersString}_{$domain}_{$language}";
    }
}
