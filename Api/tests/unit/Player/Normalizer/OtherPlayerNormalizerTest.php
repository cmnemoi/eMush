<?php

namespace Mush\Test\Player\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Normalizer\OtherPlayerNormalizer;
use PHPUnit\Framework\TestCase;

class OtherPlayerNormalizerTest extends TestCase
{
    private OtherPlayerNormalizer $normalizer;

    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);
        $this->translationService = Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new OtherPlayerNormalizer($this->translationService, $this->gearToolService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNormalizer()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName(CharacterEnum::ELEESHA)
        ;

        $player = $this->createMock(Player::class);

        $player->method('getCharacterConfig')->willReturn($characterConfig);
        $player->method('getId')->willReturn(2);
        $player->method('getStatuses')->willReturn(new ArrayCollection());
        $player->method('getSkills')->willReturn([]);
        $player->method('getTargetActions')->willReturn(new ArrayCollection());
        $player->method('getDaedalus')->willReturn($daedalus);

        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha')
            ->once()
        ;

        $this->gearToolService->shouldReceive('getActionsTools')->once()->andReturn(new ArrayCollection([]));

        $data = $this->normalizer->normalize($player, null, ['currentPlayer' => new Player()]);

        $expected = [
            'id' => 2,
            'character' => [
                'key' => CharacterEnum::ELEESHA,
                'value' => 'translated eleesha',
            ],
            'skills' => [],
            'statuses' => [],
            'actions' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
