<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class PreActionEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldRemoveTrappedStatusFromPlayerRoom(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        // given a post it in the room
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // after KT has done an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]),
            actionProvider: $postIt,
            player: $this->kuanTi,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then the trapped status should be removed from the room
        $I->assertFalse($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
    }

    public function shouldInfectPlayerInteractingWithTrappedRoom(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        // given a post it in the room
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // after KT has done an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]),
            actionProvider: $postIt,
            player: $this->kuanTi,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then KT should get a spore
        $I->assertEquals(
            expected: 1,
            actual: $this->kuanTi->getSpores(),
        );
    }
}