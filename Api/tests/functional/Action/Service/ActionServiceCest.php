<?php

namespace functional\Action\Service;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
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

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
            'actionPoint' => 10,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(5);

        $action = new Action();
        $action->setName('some name');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action);

        $I->assertEquals(5, $player->getActionPoint());

        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $player->getPlace(),
            'player' => $player,
        ]);
    }

    public function testApplyCostToPlayerWithMovementPointConversion(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
            'actionPoint' => 10,
            'movementPoint' => 0,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);

        $action = new Action();
        $action->setName('some name');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(3, $player->getMovementPoint());
    }

    public function testApplyCostToPlayerWithMovementPointConversionAndDisabledStatus(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $room,
            'daedalus' => $daedalus,
            'actionPoint' => 10,
            'movementPoint' => 0,
        ]);

        $disabled = new Status($player);
        $disabled->setName(PlayerStatusEnum::DISABLED);

        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);

        $action = new Action();
        $action->setName('some name');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(0, $player->getMovementPoint());
    }
}
