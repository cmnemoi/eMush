<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Cook;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
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

        $cookActionEntity = new Action();
        $cookActionEntity->setName(ActionEnum::COOK);

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getEquipment()->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$cookActionEntity]));

        $this->cookAction->loadParameters($cookActionEntity, $player, $gameEquipment);

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

        $cookActionEntity = new Action();
        $cookActionEntity->setName(ActionEnum::COOK);

        $this->cookAction->loadParameters($cookActionEntity, $player, $gameEquipment);

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

        $cookActionEntity = new Action();
        $cookActionEntity->setName(ActionEnum::COOK);

        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$cookActionEntity]));
        $toolEquipment->getEquipment()->setMechanics(new ArrayCollection([$tool]));

        $this->cookAction->loadParameters($cookActionEntity, $player, $gameEquipment);

        $I->assertTrue($this->cookAction->isVisible());

        $gameEquipment->getEquipment()->setName(GameRationEnum::COFFEE);

        $I->assertFalse($this->cookAction->isVisible());
    }

    private function createPlayer(Daedalus $daedalus, Place $room): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setName('character name');

        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }

    private function createEquipment(string $name, Place $place): GameEquipment
    {
        $gameEquipment = new GameEquipment($place);
        $equipment = new EquipmentConfig();
        $equipment->setName($name);
        $gameEquipment
            ->setEquipment($equipment)
            ->setHolder($place)
            ->setName($name)
        ;

        return $gameEquipment;
    }
}
