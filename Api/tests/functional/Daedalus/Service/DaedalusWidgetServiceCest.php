<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Daedalus\Service\DaedalusWidgetService;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class DaedalusWidgetServiceCest extends AbstractFunctionalTest
{
    private DaedalusWidgetService $daedalusService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusWidgetService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        /** @var ItemConfig $iTrackieConfig */
        $iTrackieConfig = $I->have(EquipmentConfig::class, ['name' => ItemEnum::ITRACKIE, 'gameConfig' => $this->daedalus->getGameConfig()]);
        $iTrackie = new GameItem($this->player1);
        $iTrackie
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($iTrackieConfig)
        ;
        $I->haveInRepository($iTrackie);
    }

    public function testGetMinimap(FunctionalTester $I)
    {
        $gravitySimulatorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => EquipmentEnum::GRAVITY_SIMULATOR . '_default']);
        $gravitySimulator = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig)
        ;
        $I->haveInRepository($gravitySimulator);

        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->player1);

        $I->assertEmpty($minimap[RoomEnum::LABORATORY]['broken_equipments']);

        // break simulator
        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::BROKEN,
            $gravitySimulator,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->player1);
        $I->assertEmpty($minimap[RoomEnum::LABORATORY]['broken_equipments']);

        // report equipment
        $reportEvent = new ApplyEffectEvent(
            $this->player1,
            $gravitySimulator,
            VisibilityEnum::PRIVATE,
            ['test'],
            new \DateTime(),
        );
        $this->eventService->callEvent($reportEvent, ApplyEffectEvent::REPORT_EQUIPMENT);

        $minimap = $this->daedalusService->getMinimap($this->daedalus, $this->player1);
        $I->assertCount(1, $minimap[RoomEnum::LABORATORY]['broken_equipments']);
    }
}
