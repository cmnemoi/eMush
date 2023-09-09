<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

class BuildActionCest
{
    private Build $buildAction;

    public function _before(FunctionalTester $I)
    {
        $this->buildAction = $I->grabService(Build::class);
    }

    public function testCanReach(FunctionalTester $I)
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);

        $buildActionEntity = new Action();
        $buildActionEntity->setActionName(ActionEnum::BUILD);

        $gameEquipment = $this->createEquipment('blueprint', $room2);

        $gameEquipment->getEquipment()->setMechanics(new ArrayCollection([
            $this->createBlueprint(['metal_scraps' => 1], $buildActionEntity),
        ]));

        $this->buildAction->loadParameters($buildActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->buildAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->buildAction->isVisible());
    }

    public function testIsBlueprint(FunctionalTester $I)
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = $this->createEquipment('blueprint', $room);

        $buildActionEntity = new Action();
        $buildActionEntity->setActionName(ActionEnum::BUILD);

        $this->buildAction->loadParameters($buildActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->buildAction->isVisible());

        $gameEquipment->getEquipment()->setMechanics(new ArrayCollection([
            $this->createBlueprint(['metal_scraps' => 1], $buildActionEntity),
        ]));

        $I->assertTrue($this->buildAction->isVisible());
    }

    private function createPlayer(Daedalus $daedalus, Place $room): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10)
        ;

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
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
        $equipment->setEquipmentName($name);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName($name)
        ;

        return $gameEquipment;
    }

    private function createBlueprint(array $ingredients, Action $buildAction, EquipmentConfig $product = null): Blueprint
    {
        if ($product === null) {
            $product = new ItemConfig();
            $product->setEquipmentName('product');
            $gameProduct = new GameItem(new Place());
            $gameProduct
                ->setEquipment($product)
                ->setName('product')
            ;
        }

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients($ingredients)
            ->setCraftedEquipmentName($product->getEquipmentName())
            ->addAction($buildAction)
        ;

        return $blueprint;
    }
}
