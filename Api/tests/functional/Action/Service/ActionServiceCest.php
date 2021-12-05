<?php

namespace functional\Action\Service;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\VisibilityEnum;

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

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(5, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $player->getPlace(),
            'player' => $player,
            'visibility' => VisibilityEnum::HIDDEN,
        ]);
    }

    public function testApplyCostToPlayerWithMovementPointConversion(FunctionalTester $I)
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
            'movementPoint' => 0,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);

        $action = new Action();
        $action->setName('some name');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(1, $player->getMovementPoint());
    }

    public function testApplyCostToPlayerWithMovementPointConversionAndModifier(FunctionalTester $I)
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
            'movementPoint' => 0,
            'characterConfig' => $characterConfig,
        ]);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;

        $I->haveInRepository($modifierConfig);

        $disabledModifier = new Modifier($player, $modifierConfig);

        $I->haveInRepository($disabledModifier);

        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);

        $action = new Action();
        $action->setName('some name');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(0, $player->getMovementPoint());
    }
}
