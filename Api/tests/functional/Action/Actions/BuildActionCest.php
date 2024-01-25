<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class BuildActionCest extends AbstractFunctionalTest
{
    private Action $buildConfig;
    private Build $buildAction;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->buildConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::BUILD]);
        $this->buildAction = $I->grabService(Build::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
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

    public function testCannotBuildASwedishSofaNotInARoom(FunctionalTester $I): void
    {
        // given I have a patrol ship place
        $patrolShipPlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        // given I have a player in this place
        $this->player->changePlace($patrolShipPlace);

        // given this player has a swedish sofa blueprint
        $swedishSofaBlueprint = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'swedish_sofa_blueprint',
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given the player also has a tube and some scrap to build the sofa
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::THICK_TUBE,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when player wants to build the sofa
        $this->buildAction->loadParameters($this->buildConfig, $this->player, $swedishSofaBlueprint);

        // then the action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::NOT_A_ROOM,
            actual: $this->buildAction->cannotExecuteReason()
        );
    }

    public function testBuildSuccess(FunctionalTester $I): void
    {
        // given I have a blueprint in room
        $thermosensorBlueprint = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::THERMOSENSOR . '_blueprint',
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given I have some ingredients to build it
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::PLASTIC_SCRAPS,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when I build the blueprint
        $this->buildAction->loadParameters($this->buildConfig, $this->player, $thermosensorBlueprint);
        $this->buildAction->execute();

        // then I have the thermosensor in my inventory
        $this->player->getEquipmentByName(ItemEnum::THERMOSENSOR);

        // then I see a private room log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => ActionLogEnum::BUILD_SUCCESS,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
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
            ->setName('blueprint')
            ->setIngredients($ingredients)
            ->setCraftedEquipmentName($product->getEquipmentName())
            ->addAction($buildAction)
        ;

        return $blueprint;
    }
}
