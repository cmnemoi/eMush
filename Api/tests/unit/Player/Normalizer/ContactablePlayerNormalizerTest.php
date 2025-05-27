<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\ContactablePlayerNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ContactablePlayerNormalizerTest extends TestCase
{
    private ContactablePlayerNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ContactablePlayerNormalizer(new FakeTranslationService());
    }

    public function testShouldNormalizePlayer(): void
    {
        // given
        $paola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::PAOLA, DaedalusFactory::createDaedalus());

        // when
        $normalizedPlayer = $this->normalizer->normalize($paola);

        // then
        self::assertEquals([
            'id' => $paola->getId(),
            'key' => 'paola',
            'name' => 'Paola',
        ], $normalizedPlayer);
    }
}

final class FakeTranslationService implements TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return 'Paola';
    }
}
