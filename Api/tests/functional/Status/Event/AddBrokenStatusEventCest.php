<?php

namespace functional\Status\Event;

use App\Tests\FunctionalTester;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;

class AddBrokenStatusEventCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDispatchEquipmentBroken(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($statusConfig);

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

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        // Case of a game Equipment
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::BROKEN,
            $gameEquipment,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $statusEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventService->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertTrue($room->getEquipments()->first()->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => StatusEventLogEnum::EQUIPMENT_BROKEN,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
