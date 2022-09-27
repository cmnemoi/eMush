<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\SelfHeal;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;

class SelfHealCest
{
    private SelfHeal $selfHealAction;

    public function _before(FunctionalTester $I)
    {
        $this->selfHealAction = $I->grabService(SelfHeal::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testSelfHeal(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(3)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::SELF_HEAL)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
            'actionPoint' => 3,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        $this->selfHealAction->loadParameters($action, $healerPlayer);

        $I->assertTrue($this->selfHealAction->isVisible());
        $I->assertNull($this->selfHealAction->cannotExecuteReason());

        $this->selfHealAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healerPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getId(),
            'player' => $healerPlayer->getId(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
