<?php

namespace functional\Action\Service;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class ActionServiceCest
{
    private ActionServiceInterface $actionService;

    public function _before(FunctionalTester $I)
    {
        $this->actionService = $I->grabService(ActionService::class);
    }

    public function testApplyCostToPlayer(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
            'actionPoint' => 10,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(5);

        $action = new Action();
        $action->setName('some name');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action);

        $I->assertEquals(5, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $player->getPlace(),
            'player' => $player,
            'visibility' => VisibilityEnum::HIDDEN,
        ]);
    }

    //@TODO test incoming in the modifier merge request where movement point conversion has been reworked
//    public function testApplyCostToPlayerWithMovementPointConversion(FunctionalTester $I)
//    {
//        /** @var Daedalus $daedalus */
//        $daedalus = $I->have(Daedalus::class);
//
//        /** @var Place $room */
//        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);
//
//        /** @var CharacterConfig $characterConfig */
//        $characterConfig = $I->have(CharacterConfig::class);
//        /** @var Player $player */
//        $player = $I->have(Player::class, [
//            'place' => $room,
//            'daedalus' => $daedalus,
//            'actionPoint' => 10,
//            'movementPoint' => 0,
//            'characterConfig' => $characterConfig,
//        ]);
//
//        $actionCost = new ActionCost();
//        $actionCost->setMovementPointCost(1);
//
//        $action = new Action();
//        $action->setName('some name');
//        $action->setActionCost($actionCost);
//
//        $this->actionService->applyCostToPlayer($player, $action);
//
//        $I->assertEquals(9, $player->getActionPoint());
//        $I->assertEquals(2, $player->getMovementPoint());
//    }
//
//    public function testApplyCostToPlayerWithMovementPointConversionAndDisabledStatus(FunctionalTester $I)
//    {
//        /** @var Daedalus $daedalus */
//        $daedalus = $I->have(Daedalus::class);
//
//        /** @var Place $room */
//        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);
//
//        /** @var CharacterConfig $characterConfig */
//        $characterConfig = $I->have(CharacterConfig::class);
//        /** @var Player $player */
//        $player = $I->have(Player::class, [
//            'place' => $room,
//            'daedalus' => $daedalus,
//            'actionPoint' => 10,
//            'movementPoint' => 0,
//            'characterConfig' => $characterConfig,
//        ]);
//
//        $disabled = new Status($player);
//        $disabled->setName(PlayerStatusEnum::DISABLED);
//
//        $actionCost = new ActionCost();
//        $actionCost->setMovementPointCost(1);
//
//        $action = new Action();
//        $action->setName('some name');
//        $action->setActionCost($actionCost);
//
//        $this->actionService->applyCostToPlayer($player, $action);
//
//        $I->assertEquals(9, $player->getActionPoint());
//        $I->assertEquals(0, $player->getMovementPoint());
//    }
}
