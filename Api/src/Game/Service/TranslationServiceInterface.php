<?php

declare(strict_types=1);

namespace Mush\Game\Service;

interface TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string;
}
