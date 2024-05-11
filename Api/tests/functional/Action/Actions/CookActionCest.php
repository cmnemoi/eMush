<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Cook;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class CookActionCest
{
    private Cook $cookAction;

    public function _before(FunctionalTester $I)
    {
        $this->cookAction = $I->grabService(Cook::class);
    }

    public function testCanReach(FunctionalTester $I)
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);
        $toolEquipment = $this->createEquipment('tool', $room1);

        $gameEquipment = $this->createEquipment(GameRationEnum::STANDARD_RATION, $room2);

        $cookActionEntity = new ActionConfig();
        $cookActionEntity
            ->setActionName(ActionEnum::COOK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getEquipment()->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment->getEquipment()->setActionConfigs(new ArrayCollection([$cookActionEntity]));

        $this->cookAction->loadParameters(
            actionConfig: $cookActionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertFalse($this->cookAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->cookAction->isVisible());
    }

    public function testUsedTool(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $toolEquipment = $this->createEquipment('tool', $room);

        $gameEquipment = $this->createEquipment(GameRationEnum::STANDARD_RATION, $room);

        $cookActionEntity = new ActionConfig();
        $cookActionEntity
            ->setActionName(ActionEnum::COOK)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::ROOM);

        $this->cookAction->loadParameters(
            actionConfig: $cookActionEntity,
            actionProvider: $toolEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertFalse($this->cookAction->isVisible());

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getEquipment()->setMechanics(new ArrayCollection([$tool]));

        $I->assertTrue($this->cookAction->isVisible());
    }

    public function testCookable(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $toolEquipment = $this->createEquipment('tool', $room);

        $gameEquipment = $this->createEquipment(GameRationEnum::STANDARD_RATION, $room);

        $cookActionEntity = new ActionConfig();
        $cookActionEntity
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionName(ActionEnum::COOK);

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getEquipment()->setMechanics(new ArrayCollection([$tool]));

        $this->cookAction->loadParameters(
            actionConfig: $cookActionEntity,
            actionProvider: $toolEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertTrue($this->cookAction->isVisible());

        $gameEquipment->getEquipment()->setEquipmentName(GameRationEnum::COFFEE);

        $I->assertFalse($this->cookAction->isVisible());
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
            ->setHolder($place)
            ->setName($name);

        return $gameEquipment;
    }
}
