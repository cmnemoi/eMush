<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\NPCMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NPCMovedEventCest extends AbstractFunctionalTest
{
    private GameEquipment $cat;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->cat = $I->grabService(GameEquipmentServiceInterface::class)->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::SPACE),
            reasons: [],
            time: new \DateTime(),
        );
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldPrintLogInOldPlace(FunctionalTester $I): void
    {
        $this->givenCatIsInRoom(RoomEnum::SPACE);

        $this->whenCatMovesFromRoom(RoomEnum::LABORATORY);

        $this->thenLogShouldBePrintedInRoom(RoomEnum::LABORATORY, LogEnum::NPC_EXITED_ROOM, $I);
    }

    public function shouldPrintLogInNewPlace(FunctionalTester $I): void
    {
        $this->givenCatIsInRoom(RoomEnum::SPACE);

        $this->whenCatMovesFromRoom(RoomEnum::LABORATORY);

        $this->thenLogShouldBePrintedInRoom(RoomEnum::SPACE, LogEnum::NPC_ENTERED_ROOM, $I);
    }

    private function givenCatIsInRoom(string $roomName): void
    {
        $room = $this->daedalus->getPlaceByName($roomName);
        $this->cat->setHolder($room);
    }

    private function whenCatMovesFromRoom(string $oldRoomName): void
    {
        $oldRoom = $this->daedalus->getPlaceByName($oldRoomName);
        $NPCMovedEvent = new NPCMovedEvent(
            NPC: $this->cat,
            oldRoom: $oldRoom,
        );
        $this->eventService->callEvent($NPCMovedEvent, NPCMovedEvent::class);
    }

    private function thenLogShouldBePrintedInRoom(string $roomName, string $logType, FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $roomName,
                'log' => $logType,
                'visibility' => VisibilityEnum::PUBLIC,
            ],
        );
        $I->assertEquals(
            expected: 'schrodinger',
            actual: $log->getParameters()['item'],
        );
    }
}
