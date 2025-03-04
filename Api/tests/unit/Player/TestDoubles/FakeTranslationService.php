<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\TestDoubles;

use Mush\Game\Service\TranslationServiceInterface;

final class FakeTranslationService implements TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return match ($key) {
            'jin_su.name' => 'Jin Su',
            'paola.name' => 'Paola',
            'commander_mission.buttons.label' => 'Accepter ?',
            'commander_mission.buttons.accept' => ':online: Oui + 3 :pa:',
            'commander_mission.buttons.reject' => ':offline: Non',
            default => "à l'instant",
        };
    }
}
