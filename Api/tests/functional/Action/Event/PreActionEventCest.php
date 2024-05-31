<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\MushMessageEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
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

        // when KT starts an action
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

        // when KT starts an action
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

    public function shouldNotInfectImmunizedPlayerInteractingWithTrappedRoom(FunctionalTester $I): void
    {
        // given Chun's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        // given chun is immunized
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::IMMUNIZED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given a post it in the room
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // when Chun starts an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]),
            actionProvider: $postIt,
            player: $this->chun,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then Chun should not get a spore
        $I->assertEquals(
            expected: 0,
            actual: $this->chun->getSpores(),
        );
    }

    public function shouldNotInfectMushPlayerInteractingWithTrappedRoom(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        // given KT is mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
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

        // when KT starts an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]),
            actionProvider: $postIt,
            player: $this->kuanTi,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then KT should not get a spore
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldPrintAMessageInMushChannelAfterPlayerInteractsWithTrappedRoom(FunctionalTester $I): void
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

        // when KT starts an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]),
            actionProvider: $postIt,
            player: $this->kuanTi,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then a message should be printed in the mush channel
        $I->haveInRepository(
            Message::class,
            [
                'message' => MushMessageEnum::INFECT_TRAP,
            ]
        );
    }

    public function shouldPrintMushChannelMessageWithRightParameters(FunctionalTester $I): void
    {   
        // given KT's room has been trapped by Gioele
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
            target: $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE)
        );

        // given a post it in the room
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // when KT starts an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]),
            actionProvider: $postIt,
            player: $this->kuanTi,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then the message should have the right parameters
        $mushChannelMessage = $I->grabEntityFromRepository(
            Message::class,
            [
                'message' => MushMessageEnum::INFECT_TRAP,
            ]
        );

        $I->assertEquals(
            expected: CharacterEnum::KUAN_TI,
            actual: $mushChannelMessage->getTranslationParameters()['target_character'],
        );

        $I->assertEquals(
            expected: CharacterEnum::GIOELE,
            actual: $mushChannelMessage->getTranslationParameters()['character'],
        );
    }

    public function shouldNotTriggerRoomTrapIfEquipmentIsNotInTheRoom(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        // given a post it in KT's inventory
        $postIt = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::POST_IT,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        // when KT starts an action
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::READ_DOCUMENT]),
            actionProvider: $postIt,
            player: $this->kuanTi,
        );
        $actionEvent->setActionResult(new Success());
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);

        // then KT should not get a spore
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldTriggerRoomTrapForSpecificActions(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]),
            actionProvider: $this->kuanTi,
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