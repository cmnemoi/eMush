<?php

declare(strict_types=1);

namespace Mush\Tests\unit\TestDoubles\Service;

use Mush\Game\Service\TranslationServiceInterface;

final class StubTranslationService implements TranslationServiceInterface
{
    public function __invoke(string $key, array $parameters = [], string $domain = 'messages', ?string $language = null): string
    {
        return $key;
    }

    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return $key;
    }
}
