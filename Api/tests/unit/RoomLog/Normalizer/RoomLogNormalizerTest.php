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

/**
 * @internal
 */
final class RoomLogNormalizerTest extends TestCase
{
    private RoomLogNormalizer $normalizer;

    /** @var Mockery\Mock|TranslationService */
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

        $roomLog1 = $this->createStub(RoomLog::class);
        $roomLog1->method('getId')->willReturn(1);
        $roomLog1->method('getLog')->willReturn('logKey1');
        $roomLog1->method('getVisibility')->willReturn(VisibilityEnum::PUBLIC);
        $roomLog1->method('getCreatedAt')->willReturn($date);
        $roomLog1->method('getParameters')->willReturn([]);
        $roomLog1->method('getDay')->willReturn(1);
        $roomLog1->method('getCycle')->willReturn(3);
        $roomLog1->method('getType')->willReturn('log');
        $roomLog1->method('getPlace')->willReturn('place');

        $roomLog2 = $this->createStub(RoomLog::class);
        $roomLog2->method('getId')->willReturn(2);
        $roomLog2->method('getLog')->willReturn('logKey2');
        $roomLog2->method('getVisibility')->willReturn(VisibilityEnum::PUBLIC);
        $roomLog2->method('getCreatedAt')->willReturn($date);
        $roomLog2->method('getParameters')->willReturn(['player' => 'andie']);
        $roomLog2->method('getDay')->willReturn(1);
        $roomLog2->method('getCycle')->willReturn(4);
        $roomLog2->method('getType')->willReturn('log');
        $roomLog2->method('getPlace')->willReturn('place');

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
            ->with('logKey1', ['is_tracker' => 'false'], 'log', LanguageEnum::FRENCH)
            ->andReturn('translated log 1')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('logKey2', ['player' => 'andie', 'is_tracker' => 'false'], 'log', LanguageEnum::FRENCH)
            ->andReturn('translated log 2')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->twice();

        $normalizeLogs = $this->normalizer->normalize($this->roomLogCollection, null, ['currentPlayer' => $this->player]);

        $expectedLogs = [1 => [
            3 => [['id' => 1, 'log' => 'translated log 1', 'visibility' => VisibilityEnum::PUBLIC, 'date' => 'translated date', 'isUnread' => false]],
            4 => [['id' => 2, 'log' => 'translated log 2', 'visibility' => VisibilityEnum::PUBLIC, 'date' => 'translated date', 'isUnread' => false]],
        ]];

        self::assertEquals($expectedLogs, $normalizeLogs);
    }
}
