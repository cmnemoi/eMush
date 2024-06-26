<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Heal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\ToolItemEnum;
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
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class HealCest extends AbstractFunctionalTest
{
    private ActionConfig $healConfig;
    private Heal $healAction;

    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->healConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HEAL]);
        $this->healAction = $I->grabService(Heal::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::HEAL)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(2)
            ->buildName(GameConfigEnum::TEST)
            ->setOutputQuantity(3);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setEquipmentName(ToolItemEnum::MEDIKIT)
            ->setActionConfigs(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $medlab,
        ]);
        $healerPlayer->setPlayerVariables($characterConfig);
        $healerPlayer
            ->setActionPoint(2);

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
            ->setHealthPoint(6);
        $healedPlayerInfo = new PlayerInfo($healedPlayer, $user, $characterConfig);
        $I->haveInRepository($healedPlayerInfo);
        $healedPlayer->setPlayerInfo($healedPlayerInfo);
        $I->refreshEntities($healedPlayer);

        $initHealthPoint = $healedPlayer->getHealthPoint();

        $this->healAction->loadParameters(
            actionConfig: $action,
            actionProvider: $healerPlayer,
            player: $healerPlayer,
            target: $healedPlayer
        );

        $I->assertTrue($this->healAction->isVisible());
        $I->assertNull($this->healAction->cannotExecuteReason());

        $this->healAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals($initHealthPoint + 3, $healedPlayer->getHealthPoint());

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

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::HEAL)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(2)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);
        $itemConfig
            ->setEquipmentName(ToolItemEnum::MEDIKIT)
            ->setActionConfigs(new ArrayCollection([$action]));

        $I->haveInRepository($itemConfig);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $healerPlayer */
        $healerPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $laboratory,
        ]);
        $healerPlayer->setPlayerVariables($characterConfig);
        $healerPlayer
            ->setActionPoint(2);

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
            ->setHealthPoint(6);
        $healedPlayerInfo = new PlayerInfo($healedPlayer, $user, $characterConfig);
        $I->haveInRepository($healedPlayerInfo);
        $healedPlayer->setPlayerInfo($healedPlayerInfo);
        $I->refreshEntities($healedPlayer);

        $this->healAction->loadParameters(
            actionConfig: $action,
            actionProvider: $healerPlayer,
            player: $healerPlayer,
            target: $healedPlayer
        );

        $I->assertFalse($this->healAction->isVisible());
    }

    public function testHealAtFullLifePrintsCorrectLog(FunctionalTester $I): void
    {
        // given players are in medlab
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $this->players->map(static function (Player $player) use ($medlab) {
            $player->changePlace($medlab);
        });

        // given player 2 has a flu
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->player2,
            reasons: []
        );

        $this->player2->setHealthPoint(14);
        $I->refreshEntities($this->player2);

        $healthVariable = $this->player2->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertTrue($healthVariable->isMax());

        // when player 1 heals player 2
        $this->healAction->loadParameters(
            actionConfig: $this->healConfig,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2
        );
        $this->healAction->execute();

        // then I don't see a log about health gained
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'log' => ActionLogEnum::HEAL_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // then I see a log about disease being cured
        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'log' => LogEnum::DISEASE_CURED_PLAYER,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
