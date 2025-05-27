<?php

namespace Mush\Tests\unit\Player\Normalizer;

use Mockery;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\OtherPlayerNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OtherPlayerNormalizerTest extends TestCase
{
    private OtherPlayerNormalizer $normalizer;

    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new OtherPlayerNormalizer($this->translationService, $this->gearToolService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testNormalizer()
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);

        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.description', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha description')
            ->once();

        $data = $this->normalizer->normalize($player, null, ['currentPlayer' => Player::createNull()]);

        $expected = [
            'id' => $player->getId(),
            'character' => [
                'key' => CharacterEnum::ELEESHA,
                'value' => 'translated eleesha',
                'description' => 'translated eleesha description',
            ],
            'statuses' => [],
            'skills' => [],
            'titles' => [],
            'actions' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }
}
