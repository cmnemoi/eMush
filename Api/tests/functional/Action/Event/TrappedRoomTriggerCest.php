<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Event;

use Mush\Action\Actions\Move;
use Mush\Action\Actions\ReadDocument;
use Mush\Action\Actions\Search;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
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

    private Take $take;
    private ReadDocument $readDocument;
    private Search $search;
    private Move $move;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->takeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->readDocumentActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::READ_DOCUMENT]);
        $this->searchActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]);
        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);

        $this->take = $I->grabService(Take::class);
        $this->readDocument = $I->grabService(ReadDocument::class);
        $this->search = $I->grabService(Search::class);
        $this->move = $I->grabService(Move::class);

        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
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
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->chun,
            target: $postIt,
        );
        $this->take->execute();

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
        $this->take->loadParameters(
            actionConfig: $this->takeActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->take->execute();

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
        $this->readDocument->loadParameters(
            actionConfig: $this->readDocumentActionConfig,
            actionProvider: $postIt,
            player: $this->kuanTi,
            target: $postIt,
        );
        $this->readDocument->execute();

        // then room trap should not be triggered
        $I->assertTrue($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));

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

        $this->search->loadParameters(
            actionConfig: $this->searchActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $this->search->execute();

        // then trap should be triggered
        $I->assertFalse($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));

        // then KT should get a spore
        $I->assertEquals(
            expected: 1,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldNotTriggerRoomTrapWhenExitingTheRoom(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

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

        // then trap should not be triggered
        $I->assertTrue($this->kuanTi->getPreviousRoom()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value), 'Room trap should not be triggered');

        // then KT should not get a spore
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
            message: 'KT should not get a spore',
        );
    }

    public function shouldNotTriggerRoomTrapWhenExitingTheRoomFromBed(FunctionalTester $I): void
    {
        // given KT's room has been trapped
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->kuanTi->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

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

        // then trap should not be triggered
        $I->assertTrue($this->kuanTi->getPreviousRoom()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value), 'Room trap should not be triggered');

        // then KT should not get a spore
        $I->assertEquals(0, $this->kuanTi->getSpores(), 'KT should not get a spore');
    }
}
