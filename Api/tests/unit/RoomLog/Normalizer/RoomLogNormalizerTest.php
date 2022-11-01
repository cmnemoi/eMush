<?php

namespace Mush\Test\RoomLog\Normalizer;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Normalizer\RoomLogNormalizer;
use PHPUnit\Framework\TestCase;

class RoomLogNormalizerTest extends TestCase
{
    private RoomLogNormalizer $normalizer;

    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new RoomLogNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testRoomLogNormalizer()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player->setCharacterConfig($playerConfig)->setDaedalus($daedalus);

        $roomLog = $this->createMock(RoomLog::class);

        $roomLog->method('getLog')->willReturn('log');
        $roomLog->method('getVisibility')->willReturn('visibility');
        $roomLog->method('getDate')->willReturn(new \DateTime());

        $this
            ->translationService
            ->shouldReceive('translate')
            ->with('log', [], '', LanguageEnum::FRENCH)
            ->andReturn('translatedLog')
        ;

        $this
            ->translationService
            ->shouldReceive('translate')
            ->with('instant', [], 'misc', LanguageEnum::FRENCH)
            ->andReturn("à l'instant")
        ;

        $data = $this->normalizer->normalize($roomLog, null, ['currentPlayer' => $player]);

        $expected = [
            'log' => 'translatedLog',
            'visibility' => 'visibility',
            'age' => "à l'instant",
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
