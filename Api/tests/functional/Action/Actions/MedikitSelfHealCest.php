<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\MedikitSelfHeal;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MedikitSelfHealCest
{
    private MedikitSelfHeal $MedikitSelfHealAction;

    public function _before(FunctionalTester $I)
    {
        $this->MedikitSelfHealAction = $I->grabService(MedikitSelfHeal::class);
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testMedikitSelfHeal(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(3)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::MEDIKIT_SELF_HEAL)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 3,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        $gameItem = new GameItem();
        $gameItem
            ->setName(ToolItemEnum::MEDIKIT)
            ->setEquipment($itemConfig)
            ->setHolder($healerPlayer)
        ;
        $I->haveInRepository($gameItem);

        $this->MedikitSelfHealAction->loadParameters($action, $healerPlayer);

        $I->assertTrue($this->MedikitSelfHealAction->isVisible());
        $I->assertNull($this->MedikitSelfHealAction->cannotExecuteReason());

        $this->MedikitSelfHealAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healerPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $healerPlayer->getId(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
