<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Heal;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class HealCest
{
    private Heal $healAction;

    public function _before(FunctionalTester $I)
    {
        $this->healAction = $I->grabService(Heal::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testHeal(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(2)
           ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setEquipmentName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
        ]);
        $healerPlayer->setPlayerVariables($characterConfig);
        $healerPlayer
            ->setActionPoint(2)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $healerPlayerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);
        $I->haveInRepository($healerPlayerInfo);
        $healerPlayer->setPlayerInfo($healerPlayerInfo);
        $I->refreshEntities($healerPlayer);

        /** @var Player $healedPlayer */
        $healedPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
        ]);
        $healedPlayer->setPlayerVariables($characterConfig);
        $healedPlayer
            ->setHealthPoint(6)
        ;
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
            'place' => $medlab->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $healerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::HEAL_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testMedlabHealOutsideMedlab(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $laboratory */
        $laboratory = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::LABORATORY]);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(2)
           ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setEquipmentName(ToolItemEnum::MEDIKIT)
            ->setActions(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actions' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
        ]);
        $healerPlayer->setPlayerVariables($characterConfig);
        $healerPlayer
            ->setActionPoint(2)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $healerPlayerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);
        $I->haveInRepository($healerPlayerInfo);
        $healerPlayer->setPlayerInfo($healerPlayerInfo);
        $I->refreshEntities($healerPlayer);

        /** @var Player $healedPlayer */
        $healedPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
        ]);
        $healedPlayer->setPlayerVariables($characterConfig);
        $healedPlayer
            ->setHealthPoint(6)
        ;
        $healedPlayerInfo = new PlayerInfo($healedPlayer, $user, $characterConfig);
        $I->haveInRepository($healedPlayerInfo);
        $healedPlayer->setPlayerInfo($healedPlayerInfo);
        $I->refreshEntities($healedPlayer);

        $this->healAction->loadParameters($action, $healerPlayer, $healedPlayer);

        $I->assertFalse($this->healAction->isVisible());
    }
}
