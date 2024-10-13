<?php

namespace Mush\Tests\functional\Communication\Listener;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventService;
use Mush\RoomLog\Listener\EquipmentSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EquipmentSubscriberCest extends AbstractFunctionalTest
{
    private $equipmentSubscriber;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventService $eventService;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentSubscriber = $I->grabService(EquipmentSubscriber::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->eventService = $I->grabService(EventService::class);
    }

    public function ifSchrodingerGetsDestroyedNeronShouldSpeak(FunctionalTester $I)
    {
        $this->givenCatIsInShelf($I);
        $this->whenCatIsDestroyed();
        $I->seeInRepository(
            Message::class,
            [
                'channel' => $this->publicChannel,
                'message' => NeronMessageEnum::SCHRODINGER_DEATH,
            ]
        );
    }

    private function givenCatIsInShelf(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenCatIsDestroyed(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            $this->schrodinger,
            null,
            VisibilityEnum::PRIVATE,
            [],
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
