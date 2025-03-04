<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\TestDoubles;

use Mush\Game\Service\TranslationServiceInterface;

final class FakeTranslationService implements TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return match ($key) {
            'derek.name' => 'Derek',
            'mush.name' => 'Mush',
            'infect_trap' => "**{$parameters['target_character']}** a été contaminé en ouvrant une étagère piégée par **{$parameters['character']}**. Son niveau de contamination est maintenant de **{$parameters['quantity']}**.",
            default => 'à l\'instant',
        };
    }
}
