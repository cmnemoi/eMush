<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\SelfHeal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
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
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class SelfHealCest extends AbstractFunctionalTest
{
    private ActionConfig $selfHealConfig;
    private SelfHeal $selfHealAction;

    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->selfHealConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SELF_HEAL]);
        $this->selfHealAction = $I->grabService(SelfHeal::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testSelfHeal(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $medlab */
        $medlab = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::MEDLAB]);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::SELF_HEAL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST)
            ->setOutputQuantity(3);
        $I->haveInRepository($action);

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
            ->setActionPoint(3)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($healerPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $healerPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($healerPlayer);

        $this->selfHealAction->loadParameters(
            actionConfig: $action,
            actionProvider: $healerPlayer,
            player: $healerPlayer
        );

        $I->assertTrue($this->selfHealAction->isVisible());
        $I->assertNull($this->selfHealAction->cannotExecuteReason());

        $this->selfHealAction->execute();

        $I->assertEquals(0, $healerPlayer->getActionPoint());
        $I->assertEquals(9, $healerPlayer->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'playerInfo' => $healerPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testHealAtFullLifePrintsCorrectLog(FunctionalTester $I): void
    {
        // given player is in medlab
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $this->player->changePlace($medlab);

        // given player has a flu
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->player,
            reasons: []
        );

        // when player heals themselves
        $this->selfHealAction->loadParameters(
            actionConfig: $this->selfHealConfig,
            actionProvider: $this->player,
            player: $this->player
        );
        $this->selfHealAction->execute();

        // then I don't see a log about health gained
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'log' => ActionLogEnum::SELF_HEAL,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // then I see a log about disease being cured
        $I->seeInRepository(RoomLog::class, [
            'place' => $medlab->getName(),
            'log' => LogEnum::DISEASE_CURED_PLAYER,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function mycologistShouldRemoveTheirSpores(FunctionalTester $I): void
    {
        $this->givenPlayerIsInMedlab($I);

        $this->givenPlayerIsAMycologist($I);

        $this->givenPlayerHasSpore(2);

        $this->whenPlayerHealsSelf();

        $this->thenPlayerShouldHaveSpore(1, $I);
    }

    public function medicShouldGiveMoreHealthPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsInMedlab($I);

        $this->givenPlayerIsAMedic($I);

        $this->givenPlayerHasHealthPoints(6);

        $this->whenPlayerHealsSelf();

        $this->thenPlayerShouldHaveHealthPoints(11, $I);
    }

    public function medicShouldNotGiveMoreHealthPointsOnFail(FunctionalTester $I): void
    {
        $this->givenPlayerIsInMedlab($I);

        $this->givenPlayerIsAMedic($I);

        $this->givenPlayerHasHealthPoints(14);

        $this->whenPlayerHealsSelf();

        $this->thenPlayerShouldHaveHealthPoints(14, $I);
    }

    private function givenPlayerIsInMedlab(FunctionalTester $I): void
    {
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $this->player->changePlace($medlab);
    }

    private function givenPlayerIsAMycologist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MYCOLOGIST, $I);
    }

    private function givenPlayerHasSpore(int $quantity): void
    {
        $this->player->setSpores($quantity);
    }

    private function givenPlayerIsAMedic(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MEDIC, $I);
    }

    private function givenPlayerHasHealthPoints(int $healthPoints): void
    {
        $this->player->setHealthPoint($healthPoints);
    }

    private function whenPlayerHealsSelf(): void
    {
        $this->selfHealAction->loadParameters(
            actionConfig: $this->selfHealConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->selfHealAction->execute();
    }

    private function thenPlayerShouldHaveSpore(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->player->getSpores());
    }

    private function thenPlayerShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->player->getHealthPoint());
    }
}
