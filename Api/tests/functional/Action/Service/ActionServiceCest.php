<?php

namespace functional\Action\Service;

use App\Tests\FunctionalTester;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourcePointChangeEvent;
use Mush\RoomLog\Entity\RoomLog;

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

    public function testApplyCostToPlayerWithMovementPointConversion(FunctionalTester $I): void
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
        $I->assertEquals(0, $player->getMovementPoint());
    }

    public function testApplyCostToPlayerWithMovementPointConversionAndModifier(FunctionalTester $I): void
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

        $modifierConfig = new ModifierConfig(
            'a random modifier config',
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $modifierConfig
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN);
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $actionCost = new ActionCost();
        $actionCost->setMovementPointCost(1);

        $action = new Action();
        $action->setName('a random action');
        $action->setActionCost($actionCost);

        $this->actionService->applyCostToPlayer($player, $action, null);

        $I->assertEquals(9, $player->getActionPoint());
        $I->assertEquals(2, $player->getMovementPoint());
    }
}
