<?php

namespace Mush\Tests\unit\RoomLog\Normalizer;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Normalizer\RoomLogNormalizer;
use PHPUnit\Framework\TestCase;

class RoomLogNormalizerTest extends TestCase
{
    private RoomLogNormalizer $normalizer;

    /** @var TranslationService|Mockery\Mock */
    private TranslationService $translationService;

    private RoomLogCollection $roomLogCollection;
    private Player $player;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = \Mockery::mock(TranslationService::class);

        $this->normalizer = new RoomLogNormalizer($this->translationService);

        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();

        $this->player = new Player();
        $this->player->setPlace($place)->setDaedalus($daedalus);

        $date = new \DateTime();

        $roomLog1 = new RoomLog();
        $roomLog1
            ->setLog('logKey1')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDate($date)
            ->setParameters([])
            ->setDay(1)
            ->setCycle(3)
            ->setType('log')
            ->setPlace('place')
        ;

        $roomLog2 = new RoomLog();
        $roomLog2
            ->setLog('logKey2')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDate($date)
            ->setParameters(['player' => 'andie'])
            ->setDay(1)
            ->setCycle(4)
            ->setType('log')
            ->setPlace('place')
        ;

        $this->roomLogCollection = new RoomLogCollection([$roomLog1, $roomLog2]);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testNormalizeRoomLogCollection()
    {
        $this->translationService
            ->shouldReceive('translate')
            ->with('logKey1', [], 'log', LanguageEnum::FRENCH)
            ->andReturn('translated log 1')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('logKey2', ['player' => 'andie'], 'log', LanguageEnum::FRENCH)
            ->andReturn('translated log 2')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->twice()
        ;

        $normalizeLogs = $this->normalizer->normalize($this->roomLogCollection, null, ['currentPlayer' => $this->player]);

        $expectedLogs = [1 => [
            3 => [['log' => 'translated log 1', 'visibility' => VisibilityEnum::PUBLIC, 'date' => 'translated date']],
            4 => [['log' => 'translated log 2', 'visibility' => VisibilityEnum::PUBLIC, 'date' => 'translated date']],
        ]];

        $this->assertEquals($expectedLogs, $normalizeLogs);
    }
}
