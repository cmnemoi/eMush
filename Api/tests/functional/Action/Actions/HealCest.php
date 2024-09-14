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
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
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
    private ChooseSkillUseCase $chooseSkillUseCase;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->healConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HEAL]);
        $this->healAction = $I->grabService(Heal::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given players are in medlab
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $this->players->map(static fn (Player $player) => $player->changePlace($medlab));
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
            'place' => $this->player->getPlace()->getName(),
            'log' => ActionLogEnum::HEAL_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // then I see a log about disease being cured
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'log' => LogEnum::DISEASE_CURED_PLAYER,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function nurseShouldNotUseActionPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsANurse($I);

        $this->givenPlayerHasTenActionPoints();

        $this->whenPlayerHeal();

        $this->thenPlayerShouldHaveTenActionPoints($I);
    }

    public function nurseShouldUseOneITPoint(FunctionalTester $I): void
    {
        $this->givenPlayerIsANurse($I);

        $this->givenPlayerHasTwoNursePoints($I);

        $this->whenPlayerHeal();

        $this->thenPlayerShouldHaveOneNursePoint($I);
    }

    public function mycologistShouldRemoveSporeFromTarget(FunctionalTester $I): void
    {
        $this->givenChunIsAMycologist($I);

        $this->givenKuanTiHasASpore();

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveSpore(0, $I);
    }

    public function mushMycologistShouldNotRemoveSporeFromTarget(FunctionalTester $I): void
    {
        $this->givenChunIsAMycologist($I);

        $this->givenChunIsMush();

        $this->givenKuanTiHasASpore();

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveSpore(1, $I);
    }

    private function givenPlayerIsANurse(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::NURSE]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::NURSE, $this->player));
    }

    private function givenPlayerHasTwoNursePoints(FunctionalTester $I): void
    {
        $I->assertEquals(2, $this->player->getSkillByNameOrThrow(SkillEnum::NURSE)->getSkillPoints());
    }

    private function givenPlayerHasTenActionPoints(): void
    {
        $this->player->setActionPoint(10);
    }

    private function givenChunIsAMycologist(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::MYCOLOGIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::MYCOLOGIST, $this->player));
    }

    private function givenKuanTiHasASpore(): void
    {
        $this->kuanTi->setSpores(1);
    }

    private function givenChunIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerHeal(): void
    {
        $this->healAction->loadParameters(
            actionConfig: $this->healConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->player2
        );
        $this->healAction->execute();
    }

    private function whenChunHealsKuanTi(): void
    {
        $this->healAction->loadParameters(
            actionConfig: $this->healConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
        $this->healAction->execute();
    }

    private function thenPlayerShouldHaveTenActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(10, $this->player->getActionPoint());
    }

    private function thenPlayerShouldHaveOneNursePoint(FunctionalTester $I): void
    {
        $I->assertEquals(1, $this->player->getSkillByNameOrThrow(SkillEnum::NURSE)->getSkillPoints());
    }

    private function thenKuanTiShouldHaveSpore(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->kuanTi->getSpores());
    }
}
