<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class MoveCest extends AbstractFunctionalTest
{
    private Action $moveConfig;
    private Move $moveAction;
    private Player $derek;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        // given those players exist
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
        $kuanTi = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::KUAN_TI);
        $this->players->add($this->derek);
        $this->players->add($jinSu);
        $this->players->add($kuanTi);

        // given there is an Icarus Bay in this Daedalus
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);

        $this->moveConfig = $I->grabEntityFromRepository(Action::class, ['name' => 'move']);
        $this->moveAction = $I->grabService(Move::class);
    }

    public function testMoveActionNotExecutableIfIcarusBayHasTooMuchPeopleInside(FunctionalTester $I): void
    {   
        // given there is a door leading to Icarus Bay
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = new Door($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY))
        ;
        $I->haveInRepository($door);

        // given all 4 players except derek are in Icarus Bay
        /** @var Player $player */
        foreach ($this->players as $player) {
            if ($player->getName() === $this->derek->getName()) {
                continue;
            }
            $player->changePlace($this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY));
        }

        $icarusBay = $this->daedalus->getPlaceByName(RoomEnum::ICARUS_BAY);

        // when derek tries to move to Icarus Bay
        $this->moveAction->loadParameters($this->moveConfig, $this->derek, $door);
        $this->moveAction->execute();

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CANNOT_GO_TO_THIS_ROOM,
            actual: $this->moveAction->cannotExecuteReason(),
        );
    }

}