<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Event;

use Mush\Action\Actions\Move;
use Mush\Action\Actions\ReadDocument;
use Mush\Action\Actions\Repair;
use Mush\Action\Actions\Search;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TrappedRoomTriggerCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private ActionConfig $takeActionConfig;
    private ActionConfig $readDocumentActionConfig;
    private ActionConfig $searchActionConfig;
    private ActionConfig $moveActionConfig;
    private ActionConfig $repairActionConfig;

    private Take $take;
    private ReadDocument $readDocument;
    private Search $search;
    private Move $move;
    private Repair $repair;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->takeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->readDocumentActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::READ_DOCUMENT]);
        $this->searchActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]);
        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);
        $this->repairActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'repair_percent_25']);
        $this->repairActionConfig->setSuccessRate(100);

        $this->take = $I->grabService(Take::class);
        $this->readDocument = $I->grabService(ReadDocument::class);
        $this->search = $I->grabService(Search::class);
        $this->move = $I->grabService(Move::class);
        $this->repair = $I->grabService(Repair::class);

        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
    }

    public function shouldRemoveTrappedStatusFromPlayerRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->kuanTi->getPlace());

        // when KT starts an action
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->take->execute();

        // then the trapped status should be removed from the room
        $I->assertFalse($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
    }

    public function shouldInfectPlayerInteractingWithTrappedRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->kuanTi->getPlace());

        // when KT starts an action
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->take->execute();

        // then KT should get a spore
        $I->assertEquals(
            expected: 1,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldDestroyTrapWhenImmunizedPlayerInteractsWithTrappedRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->chun->getPlace());

        // given chun is immunized
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::IMMUNIZED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->chun->getPlace());

        // when Chun starts an action
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->chun,
            target: $postIt,
        );
        $this->take->execute();

        // then the trapped status should be removed from the room
        $I->assertFalse($this->chun->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));

        // then Chun should not get a spore
        $I->assertEquals(
            expected: 0,
            actual: $this->chun->getSpores(),
        );
    }

    public function shouldDestroyTrapWhenMushPlayerInteractsWithTrappedRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        // given KT is mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->kuanTi->getPlace());

        // when KT starts an action
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->take->execute();

        // then the trapped status should be removed from the room
        $I->assertFalse($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));

        // then KT should not get a spore
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldPrintAMessageInMushChannelAfterPlayerInteractsWithTrappedRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->kuanTi->getPlace());

        // when KT starts an action
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->take->execute();

        // then a message should be printed in the mush channel
        $I->seeInRepository(
            Message::class,
            [
                'message' => MushMessageEnum::INFECT_TRAP,
            ]
        );
    }

    public function shouldPrintMushChannelMessageWithRightParameters(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrappedByGioele($this->kuanTi->getPlace(), $I);

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->kuanTi->getPlace());

        // when KT starts an action
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->take->execute();

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
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        $postIt = $this->givenEquipmentHeldBy(ItemEnum::POST_IT, $this->kuanTi);

        // when KT starts an action
        $this->readDocument->loadParameters(
            actionConfig: $this->readDocumentActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->readDocument->execute();

        $this->thenTrapInRoomDidntTriggerOn($this->kuanTi->getPlace(), $this->kuanTi, $I);
    }

    public function shouldTriggerRoomTrapForSpecificActions(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        $this->search->loadParameters(
            actionConfig: $this->searchActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $this->search->execute();

        $this->thenTrapInRoomTriggeredOn($this->kuanTi->getPlace(), $this->kuanTi, $I);
    }

    public function shouldNotTriggerRoomTrapWhenExitingTheRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        // given a door in the room
        $door = Door::createFromRooms($this->kuanTi->getPlace(), $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        // when KT starts an action
        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->move->execute();

        $this->thenTrapInRoomDidntTriggerOn($this->kuanTi->getPreviousRoom(), $this->kuanTi, $I);
    }

    public function shouldNotTriggerRoomTrapWhenExitingTheRoomFromBed(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        // given KT is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given a door in the room
        $door = Door::createFromRooms($this->kuanTi->getPlace(), $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        // when KT exits the room
        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->move->execute();

        $this->thenTrapInRoomDidntTriggerOn($this->kuanTi->getPreviousRoom(), $this->kuanTi, $I);
    }

    public function shouldTriggerRoomTrapWhenRepairingADoor(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        // given a BROKEN door in the room
        $door = Door::createFromRooms($this->kuanTi->getPlace(), $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime(),
        );

        // when KT starts an action
        $this->repair->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->repair->execute();

        $this->thenTrapInRoomTriggeredOn($this->kuanTi->getPlace(), $this->kuanTi, $I);
    }

    public function shouldTriggerRoomTrapWhenRepairingADoorFromOtherSide(FunctionalTester $I): void
    {
        $this->givenRoomHasBeenTrapped($this->kuanTi->getPlace());

        // given a BROKEN door in the room with room1 and room2 swapped
        $door = Door::createFromRooms($this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus), $this->kuanTi->getPlace());
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime(),
        );

        // when KT starts an action
        $this->repair->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->repair->execute();

        $this->thenTrapInRoomTriggeredOn($this->kuanTi->getPlace(), $this->kuanTi, $I);
    }

    public function shouldNotTriggerRoomTrapWhenTrapIsOnOtherSideOfDoor(FunctionalTester $I): void
    {
        // given a BROKEN door in the room with room1 and room2 swapped
        $door = Door::createFromRooms($this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus), $this->kuanTi->getPlace());
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime(),
        );

        $this->givenRoomHasBeenTrapped($this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR));

        // when KT starts an action
        $this->repair->loadParameters(
            actionConfig: $this->repairActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->repair->execute();

        $this->thenTrapInRoomDidntTriggerOn($this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR), $this->kuanTi, $I);
    }

    private function givenRoomHasBeenTrapped(Place $room): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $room,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenRoomHasBeenTrappedByGioele(Place $room, FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $room,
            tags: [],
            time: new \DateTime(),
            target: $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE)
        );
    }

    private function givenEquipmentHeldBy(string $equipment, EquipmentHolderInterface $holder): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $equipment,
            equipmentHolder: $holder,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function thenTrapInRoomDidntTriggerOn(Place $room, Player $player, FunctionalTester $I): void
    {
        $I->assertTrue($room->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));

        $I->assertEquals(0, $player->getSpores());
    }

    private function thenTrapInRoomTriggeredOn(Place $room, Player $player, FunctionalTester $I): void
    {
        $I->assertFalse($room->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));

        $I->assertEquals(1, $player->getSpores());
    }
}
