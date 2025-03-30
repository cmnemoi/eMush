<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerEventSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function shouldDeletePersonalItemFromInventory(FunctionalTester $I): void
    {
        $playerRoom = $this->player->getPlace();
        $this->givenPlayerHasItemsInInventory([ItemEnum::WALKIE_TALKIE, ItemEnum::BLASTER]);
        $this->whenPlayerDies();
        $this->thenRoomShouldHaveEquipmentOfTheFollowingAmount(1, $playerRoom, $I);
    }

    public function shouldDeletePersonalItemFromDaedalus(FunctionalTester $I): void
    {
        $playerRoom = $this->player->getPlace();
        $this->givenPlayerHasItemsInInventory([ItemEnum::WALKIE_TALKIE]);
        $walkieTalkie = $this->player->getEquipments()->first();
        $this->whenItemIsMovedToTheRoom($playerRoom, $walkieTalkie);
        $this->thenRoomShouldHaveEquipmentOfTheFollowingAmount(1, $playerRoom, $I);
        $this->whenPlayerDies();
        $this->thenRoomShouldHaveEquipmentOfTheFollowingAmount(0, $playerRoom, $I);
    }

    private function givenPlayerHasItemsInInventory(array $itemsNames): void
    {
        foreach ($itemsNames as $itemName) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $itemName,
                equipmentHolder: $this->player,
                reasons: [],
                time: new \DateTime()
            );
        }
    }

    private function whenPlayerDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::DEPRESSION,
            time: new \DateTime(),
        );
    }

    private function whenItemIsMovedToTheRoom(Place $room, GameEquipment $item): void
    {
        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $item,
            newHolder: $room,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: ['drop'],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }

    private function thenRoomShouldHaveEquipmentOfTheFollowingAmount(int $expectedCount, Place $room, FunctionalTester $I): void
    {
        $I->assertCount($expectedCount, $room->getEquipments(), "The room should have {$expectedCount} equipment, got {$room->getEquipments()->count()}");
    }
}
