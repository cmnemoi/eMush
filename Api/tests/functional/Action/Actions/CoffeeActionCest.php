<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class CoffeeActionCest
{
    private Coffee $coffeeAction;

    public function _before(FunctionalTester $I)
    {
        $this->coffeeAction = $I->grabService(Coffee::class);
    }

    public function testCanReach(FunctionalTester $I)
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);

        $gameEquipment = $this->createEquipment('coffee_machine', $room2);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity
            ->setActionName(ActionEnum::COFFEE);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->coffeeAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->coffeeAction->isVisible());
    }

    public function testHasAction(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = $this->createEquipment('coffee_machine', $room);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity->setActionName(ActionEnum::COFFEE);

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->coffeeAction->isVisible());

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $I->assertTrue($this->coffeeAction->isVisible());
    }

    public function testBroken(FunctionalTester $I)
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = $this->createEquipment('coffee_machine', $room);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity->setActionName(ActionEnum::COFFEE);

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC);
        $status = new Status($gameEquipment, $statusConfig);

        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->coffeeAction->cannotExecuteReason());
    }

    public function testNotCharged(FunctionalTester $I)
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = $this->createEquipment('coffee_machine', $room);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity->setActionName(ActionEnum::COFFEE);

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategies([ActionEnum::COFFEE]);

        $chargeStatus = new ChargeStatus($gameEquipment, $statusConfig);
        $chargeStatus
            ->setCharge(0);

        $I->assertEquals(ActionImpossibleCauseEnum::DAILY_LIMIT, $this->coffeeAction->cannotExecuteReason());
    }

    private function createPlayer(Daedalus $daedalus, Place $room): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
            ->setPlace($room);

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }

    private function createEquipment(string $name, Place $place): GameEquipment
    {
        $gameEquipment = new GameEquipment($place);
        $equipment = new EquipmentConfig();
        $equipment->setEquipmentName($name);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName($name);

        return $gameEquipment;
    }
}
