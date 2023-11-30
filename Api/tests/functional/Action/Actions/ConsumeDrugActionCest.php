<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\ConsumeDrug;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class ConsumeDrugActionCest
{
    private ConsumeDrug $consumeAction;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->consumeAction = $I->grabService(ConsumeDrug::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testConsumeDrug(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($gameConfig->getDaedalusConfig());
        $daedalus->setOxygen(1);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::SPACE]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5)
        ;
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST)
        ;

        $I->haveInRepository($consumeActionEntity);

        $ration = new Drug();
        $ration
            ->setActions(new ArrayCollection([$consumeActionEntity]))
            ->setName(GameRationEnum::STANDARD_RATION . '_' . GameConfigEnum::TEST)
        ;
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(0)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setRation($ration)
        ;
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'name' => GameRationEnum::STANDARD_RATION,
        ]);

        $I->haveInRepository($equipmentConfig);

        $gameConfig->addEquipmentConfig($equipmentConfig);
        $I->refreshEntities($gameConfig);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem);

        $gameItem2 = new GameItem($room);
        $gameItem2
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem2);

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);

        $this->consumeAction->execute();

        $I->assertEquals(0, $player->getSatiety());
        $I->assertEquals(1, $player->getStatuses()->count());
        $I->assertEquals(7, $player->getActionPoint());
        $I->assertEquals(8, $player->getMovementPoint());
        $I->assertEquals(9, $player->getMoralPoint());
        $I->assertEquals(10, $player->getHealthPoint());
        $I->assertEquals(1, $room->getEquipments()->count());

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem2);
        $I->assertNotNull($this->consumeAction->cannotExecuteReason());

        // new Cycle
        $event = new DaedalusCycleEvent(
            $daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(0, $player->getStatuses()->count());

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem2);

        $I->assertNull($this->consumeAction->cannotExecuteReason());
    }
}
