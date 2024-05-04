<?php

namespace Mush\Tests\functional\Disease\Service;

use Mush\Action\Actions\Move;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AgoraphobiaActionCest extends AbstractFunctionalTest
{
    private ActionConfig $searchConfig;
    private Search $searchAction;

    private ActionConfig $moveConfig;
    private Move $moveAction;

    private Door $door;

    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->searchConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]);
        $this->searchAction = $I->grabService(Search::class);

        $this->moveConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);
        $this->moveAction = $I->grabService(Move::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);

        // given player has the agoraphobia disease
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::AGORAPHOBIA,
            player: $this->player,
            reasons: [],
        );

        // given player has 2 action point and 2 movement point
        $this->player->setActionPoint(2);
        $this->player->setMovementPoint(2);

        // given there are 2 extra players in room (so 4 in total)
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHUN);

        // given there is a door to front corridor in the room
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $this->door = new Door($this->player->getPlace());
        $this->door
            ->setName('door_default')
            ->setEquipment($doorConfig)
            ->addRoom($frontCorridor);
        $I->haveInRepository($this->door);
    }

    public function testAgoraphobia(FunctionalTester $I)
    {
        // when player executes search action
        $this->searchAction->loadParameters($this->searchConfig, $this->player);
        $this->searchAction->execute();

        // then player has 0 action point and 2 movement point
        $I->assertEquals(0, $this->player->getActionPoint());
        $I->assertEquals(2, $this->player->getMovementPoint());
    }

    public function testAgoraphobiaForMoveAction(FunctionalTester $I)
    {
        // when player executes move action
        $this->moveAction->loadParameters($this->moveConfig, $this->player, $this->door);
        $this->moveAction->execute();

        // then player has 2 action point and 0 movement points
        $I->assertEquals(2, $this->player->getActionPoint());
        $I->assertEquals(0, $this->player->getMovementPoint());
    }

    public function testAgoraphobiaForMoveActionWithConversion(FunctionalTester $I)
    {
        // given player has 2 action point and 2 movement point
        $this->player->setActionPoint(2);
        $this->player->setMovementPoint(0);

        // when player executes move action
        $this->moveAction->loadParameters($this->moveConfig, $this->player, $this->door);
        $this->moveAction->execute();

        // then player has 1 action point and 1 movement point
        $I->assertEquals(1, $this->player->getActionPoint());
        $I->assertEquals(1, $this->player->getMovementPoint());
    }
}
