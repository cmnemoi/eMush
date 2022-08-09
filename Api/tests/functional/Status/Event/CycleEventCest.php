<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Listener\StatusCycleSubscriber;

class CycleEventCest
{
    private StatusCycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(StatusCycleSubscriber::class);
    }

    // tests
    public function testChargeStatusCycleSubscriber(FunctionalTester $I)
    {
        // Cycle Increment
        $daedalus = new Daedalus();
        $time = new DateTime();
        $player = $I->have(Player::class);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(1)
            ->setAutoRemove(true)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
        ;
        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($player, $statusConfig);
        $status
            ->setCharge(0)
        ;

        $I->haveInRepository($status);
        $id = $status->getId();

        $cycleEvent = new StatusCycleEvent($status, new Player(), EventEnum::NEW_CYCLE, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->dontSeeInRepository(ChargeStatus::class, ['id' => $id]);
    }

    public function testFireStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'propagatingFireRate' => 100,
            'hullFireDamageRate' => 100, ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['isFireBreakable' => false, 'isFireDestroyable' => false, 'gameConfig' => $gameConfig]);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(StatusEnum::FIRE)
            ->setGameConfig($gameConfig)
            ->setModifierConfigs(new ArrayCollection([]))
        ;
        $I->haveInRepository($statusConfig);

        $doorConfig
            ->setGameConfig($daedalus->getGameConfig())
            ->setIsFireBreakable(false)
            ->setIsFireDestroyable(false);

        $door = new Door();
        $door
             ->setName('door name')
             ->setEquipment($doorConfig)
        ;

        $room->addDoor($door);
        $room2->addDoor($door);

        $healthPointBefore = $player->getHealthPoint();
        $moralPointBefore = $player->getMoralPoint();
        $hullPointBefore = $daedalus->getHull();

        $time = new DateTime();

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setName(StatusEnum::FIRE);
        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(1)
        ;
        $I->haveInRepository($status);

        $cycleEvent = new StatusCycleEvent($status, $room, EventEnum::NEW_CYCLE, $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($healthPointBefore - 2, $player->getHealthPoint());
        $I->assertEquals($moralPointBefore, $player->getMoralPoint());
        $I->assertEquals($hullPointBefore - 2, $daedalus->getHull());

        $I->assertEquals(StatusEnum::FIRE, $room2->getStatuses()->first()->getName());
        $I->assertEquals(0, $room2->getStatuses()->first()->getCharge());
    }
}
