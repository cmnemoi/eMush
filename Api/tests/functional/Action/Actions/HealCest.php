<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Heal;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HealCest
{
    private Heal $healAction;

    public function _before(FunctionalTester $I)
    {
        $this->healAction = $I->grabService(Heal::class);
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testHeal(FunctionalTester $I)
    {
        $I->loadFixtures(GameConfigFixtures::class);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $healerPlayerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);
        $I->haveInRepository($healerPlayerInfo);
        $healerPlayer->setPlayerInfo($healerPlayerInfo);
        $I->refreshEntities($healerPlayer);

        /** @var Player $healedPlayer */
        $healedPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
            'healthPoint' => 6,
        ]);
        $healedPlayerInfo = new PlayerInfo($healedPlayer, $user, $characterConfig);
        $I->haveInRepository($healedPlayerInfo);
        $healedPlayer->setPlayerInfo($healedPlayerInfo);
        $I->refreshEntities($healedPlayer);

        $this->healAction->loadParameters($action, $healerPlayer, $healedPlayer);

        $I->assertTrue($this->healAction->isVisible());
        $I->assertNull($this->healAction->cannotExecuteReason());

        $this->healAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healedPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getId(),
            'playerInfo' => $healerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::HEAL_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testMedlabHealOutsideMedlab(FunctionalTester $I)
    {
        $I->loadFixtures(GameConfigFixtures::class);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $laboratory */
        $laboratory = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::LABORATORY]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(2)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $healerPlayerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);
        $I->haveInRepository($healerPlayerInfo);
        $healerPlayer->setPlayerInfo($healerPlayerInfo);
        $I->refreshEntities($healerPlayer);

        /** @var Player $healedPlayer */
        $healedPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
            'healthPoint' => 6,
        ]);
        $healedPlayerInfo = new PlayerInfo($healedPlayer, $user, $characterConfig);
        $I->haveInRepository($healedPlayerInfo);
        $healedPlayer->setPlayerInfo($healedPlayerInfo);
        $I->refreshEntities($healedPlayer);

        $this->healAction->loadParameters($action, $healerPlayer, $healedPlayer);

        $I->assertFalse($this->healAction->isVisible());
    }
}
