<?php

namespace functional\Game\Service;

use App\Tests\FunctionalTester;
use DateTime;
use Symfony\Bridge\PhpUnit\ClockMock;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusCycleSubscriber;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\DoorEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

class CycleServiceCest
{
    private DaedalusCycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(DaedalusCycleSubscriber::class);
    }

    public function testUpdateDaedalus(FunctionalTester $I)
    {
        $createdAt = new \DateTime('2020-10-10 00:30:00.0 Europe/Paris');
        $lastupdatedAt = new \DateTime('2020-10-10 00:30:00.0 Europe/Paris');
        $nowDate = $lastupdatedAt->add(new \DateInterval('PT1800M')); //add 10 cycles
        $timeZone = 'Europe/Paris';

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['cyclePerGameDay' => 8, 'cycleLength' => 180, 'timezone' => $timeZone]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig,
                                                'createdAt' => $createdAt, 
                                                'lastUpdatedAt' => $lastupdatedAt,
                                                'cycle' => 1,
                                                'day' => 1]
                                                );

        

        ClockMock::register(CycleService::class);

        $this->cycleService->handleCycleChange($daedalus);

        
    }
}
