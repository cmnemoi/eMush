<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MoveCest extends AbstractFunctionalTest
{
    private ActionConfig $moveConfig;
    private Move $moveAction;
    private Player $derek;

    private ChannelServiceInterface $channelService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        // given those players exist
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
        $kuanTi = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::KUAN_TI);
        $this->players->add($jinSu);
        $this->players->add($kuanTi);

        // given there is an Icarus Bay in this Daedalus
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        $this->moveConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'move']);
        $this->moveAction = $I->grabService(Move::class);

        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testMoveActionNotExecutableIfIcarusBayHasTooMuchPeopleInside(FunctionalTester $I): void
    {
        // given there is a door leading to Icarus Bay
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = new Door($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));
        $I->haveInRepository($door);

        // given all 4 players except derek are in Icarus Bay
        /** @var Player $player */
        foreach ($this->players as $player) {
            $player->changePlace($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));
        }

        // when derek tries to move to Icarus Bay
        $this->moveAction->loadParameters($this->moveConfig, $this->derek, $door);
        $this->moveAction->execute();

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CANNOT_GO_TO_THIS_ROOM,
            actual: $this->moveAction->cannotExecuteReason(),
        );
    }

    public function testMoveActionExecutableInIcarusBayIfTooMuchPeopleInside(FunctionalTester $I): void
    {
        // given all players are in Icarus Bay
        /** @var Player $player */
        foreach ($this->players as $player) {
            $player->changePlace($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));
        }

        // given there is a door for exiting Icarus Bay
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = new Door($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));
        $door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $I->haveInRepository($door);

        // when jinsu tries to move to the laboratory
        $jinsu = $this->players->filter(static fn (Player $player) => $player->getName() === CharacterEnum::JIN_SU)->first();
        $this->moveAction->loadParameters($this->moveConfig, $jinsu, $door);
        $this->moveAction->execute();

        // then jin su is in the laboratory
        $I->assertEquals(
            expected: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->getName(),
            actual: $jinsu->getPlace()->getName(),
        );
    }

    public function testMoveActionExecutableInOtherRoomsIfTooMuchPeopleInIcarusBay(FunctionalTester $I): void
    {
        // given 4 players are in Icarus Bay
        /** @var Player $player */
        foreach ($this->players as $player) {
            $player->changePlace($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));
        }

        // given there is Front Corridor place
        $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);

        // given there is a door for exiting laboratory to front corridor
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = new Door($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR));
        $I->haveInRepository($door);

        // when derek tries to move to the front corridor
        $this->moveAction->loadParameters($this->moveConfig, $this->derek, $door);
        $this->moveAction->execute();

        // then derek is in the front corridor
        $I->assertEquals(
            expected: $this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR)->getName(),
            actual: $this->derek->getPlace()->getName(),
        );
    }

    public function testMoveToEmptyRoomExpulsesPlayerFromPrivateChannelIfTheyDoNotHaveATalkie(FunctionalTester $I): void
    {
        // given a private channel between player1 and player2
        $channel = $this->channelService->createPrivateChannel($this->player);
        $this->channelService->addPlayer($this->player2->getPlayerInfo(), $channel);

        // given player1 has no talkie
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // given there is a door for exiting laboratory to front corridor
        $door = $this->createDoorFromLaboratoryToFrontCorridor($I);

        // when player1 moves to the front corridor
        $this->moveAction->loadParameters($this->moveConfig, $this->player, $door);
        $this->moveAction->execute();

        // then player1 should not be in the private channel anymore
        $I->assertFalse($channel->getParticipants()->map(static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer())->contains($this->player));
    }

    public function testDisabledCrewmateDoesNotConvertAPToMPWithPlayerInTheRoomAndSimulatorIsBroken(FunctionalTester $I): void
    {
        // given a simulator in the laboratory
        $simulator = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::GRAVITY_SIMULATOR,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime(),
        );

        // given this simulator is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $simulator,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is disabled
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DISABLED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun has 1 AP and 0 MP
        $this->chun->setActionPoint(1);
        $this->chun->setMovementPoint(0);

        // given there is a door for exiting laboratory to front corridor
        $door = $this->createDoorFromLaboratoryToFrontCorridor($I);

        // when Chun moves to the front corridor
        $this->moveAction->loadParameters($this->moveConfig, $this->chun, $door);
        $this->moveAction->execute();

        // then Chun should not convert AP to MP, so she should have the same amount of AP and MP
        $I->assertEquals(
            expected: 1,
            actual: $this->chun->getActionPoint(),
        );
        $I->assertEquals(
            expected: 0,
            actual: $this->chun->getMovementPoint(),
        );
    }

    private function createDoorFromLaboratoryToFrontCorridor(FunctionalTester $I): Door
    {
        $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = new Door($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR));
        $I->haveInRepository($door);

        return $door;
    }
}
