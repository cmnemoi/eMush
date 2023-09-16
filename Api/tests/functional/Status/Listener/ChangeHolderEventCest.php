<?php

namespace Mush\Tests\functional\Status\Listener;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class ChangeHolderEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDroppingOtherEquipmentWithBurdenedStatus(FunctionalTester $I)
    {
        $player = $this->player2;

        $burdenedStatusConfig = $I->grabEntityFromRepository(
            entity: StatusConfig::class,
            params: ['name' => PlayerStatusEnum::BURDENED . '_default']
        );
        $heavyStatusConfig = $I->grabEntityFromRepository(
            entity: StatusConfig::class,
            params: ['name' => EquipmentStatusEnum::HEAVY . '_default']
        );

        $superFreezerEquipmentConfig = $I->grabEntityFromRepository(
            entity: EquipmentConfig::class,
            params: ['name' => ToolItemEnum::SUPERFREEZER . '_default']
        );
        $talkieEquipmentConfig = $I->grabEntityFromRepository(
            entity: EquipmentConfig::class,
            params: ['name' => ItemEnum::WALKIE_TALKIE . '_default']
        );
        $superFreezer = new GameEquipment($player);
        $superFreezer
             ->setName(ToolItemEnum::SUPERFREEZER)
             ->setEquipment($superFreezerEquipmentConfig)
        ;
        $I->haveInRepository($superFreezer);
        $talkie = new GameItem($player);
        $talkie
             ->setName(ItemEnum::WALKIE_TALKIE)
             ->setEquipment($talkieEquipmentConfig)
        ;
        $I->haveInRepository($talkie);

        $burdenedStatus = new Status($player, $burdenedStatusConfig);
        $I->haveInRepository($burdenedStatus);
        $heavyStatus = new Status($superFreezer, $heavyStatusConfig);
        $I->haveInRepository($heavyStatus);

        $I->refreshEntities([$player, $superFreezer, $talkie]);

        $equipmentEvent = new MoveEquipmentEvent(
            $talkie,
            $player,
            $player,
            VisibilityEnum::HIDDEN,
            ['drop'],
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);

        $I->assertTrue($player->hasStatus(PlayerStatusEnum::BURDENED));
    }
}
