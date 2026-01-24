<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Heal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HealCest extends AbstractFunctionalTest
{
    private ActionConfig $healConfig;
    private Heal $healAction;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->healConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HEAL]);
        $this->healAction = $I->grabService(Heal::class);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        // given players are in medlab
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $this->players->map(static fn (Player $player) => $player->changePlace($medlab));
    }

    public function testHeal(FunctionalTester $I)
    {
        $this->givenKuanTiHasHealthPoints(1);

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(4, $I);

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::HEAL_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testMedikitHeal(FunctionalTester $I)
    {
        // given players are in the laboratory
        $place = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->player1->changePlace($place);
        $this->player2->changePlace($place);

        // given chun has the medikit
        $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::MEDIKIT,
            $this->player,
            [],
            new \DateTime(),
        );

        $this->givenKuanTiHasHealthPoints(1);

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(4, $I);
    }

    public function testHealShouldNotBeExecutableWithoutMedkitOrMedlab(FunctionalTester $I): void
    {
        // given players are in the laboratory
        $place = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->player1->changePlace($place);
        $this->player2->changePlace($place);

        // given chun do not have the medikit
        $I->assertFalse($this->player->hasEquipmentByName(ToolItemEnum::MEDIKIT));

        $this->givenKuanTiHasHealthPoints(1);

        $this->healAction->loadParameters(
            $this->healConfig,
            $this->chun,
            $this->chun,
            $this->kuanTi,
        );

        $I->assertFalse($this->healAction->isVisible());
        $I->assertNotNull($this->healAction->cannotExecuteReason());
    }

    public function testHealAtFullLifePrintsCorrectLog(FunctionalTester $I): void
    {
        // given Kuan Ti has a flu
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU->toString(),
            player: $this->player2,
            reasons: []
        );

        $this->givenKuanTiHasHealthPoints(14);
        $healthVariable = $this->player2->getVariableByName(PlayerVariableEnum::HEALTH_POINT);
        $I->assertTrue($healthVariable->isMax());

        $this->whenChunHealsKuanTi();

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

    public function nurseShouldUseOneNursePointInsteadOfActionPoints(FunctionalTester $I): void
    {
        $this->givenChunIsANurse($I);

        $this->givenChunHasTenActionPoints();

        $this->givenChunHasTwoNursePoints($I);

        $this->whenChunHealsKuanTi();

        $this->thenChunShouldHaveOneNursePoint($I);

        $this->thenChunShouldHaveTenActionPoints($I);
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

    public function medicShouldGiveMoreHealthPoints(FunctionalTester $I): void
    {
        $this->givenChunIsAMedic($I);

        $this->givenKuanTiHasHealthPoints(6);

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(11, $I);
    }

    public function medicShouldNotGiveMoreHealthPointsOnFail(FunctionalTester $I): void
    {
        $this->givenChunIsAMedic($I);

        $this->givenKuanTiHasHealthPoints(14);

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(14, $I);
    }

    public function ultraHealingPomadeShouldGiveMoreHealthPoints(FunctionalTester $I): void
    {
        $this->givenUltraHealingPomadeIsCompleted($I);

        $this->givenKuanTiHasHealthPoints(6);

        $this->whenChunHealsKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(10, $I);
    }

    private function givenChunIsANurse(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::NURSE, $I, $this->player);
    }

    private function givenChunHasTwoNursePoints(FunctionalTester $I): void
    {
        $this->player->getChargeStatusByName(SkillPointsEnum::NURSE_POINTS->toString())->setCharge(2);
    }

    private function givenChunHasTenActionPoints(): void
    {
        $this->player->setActionPoint(10);
    }

    private function givenChunIsAMycologist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MYCOLOGIST, $I, $this->chun);
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

    private function givenChunIsAMedic(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MEDIC, $I);
    }

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->player2->setHealthPoint($healthPoints);
    }

    private function givenUltraHealingPomadeIsCompleted(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::ULTRA_HEALING_POMADE),
            author: $this->player,
            I: $I,
        );
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

    private function thenChunShouldHaveTenActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(10, $this->player->getActionPoint());
    }

    private function thenChunShouldHaveOneNursePoint(FunctionalTester $I): void
    {
        $I->assertEquals(1, $this->player->getSkillByNameOrThrow(SkillEnum::NURSE)->getSkillPoints());
    }

    private function thenKuanTiShouldHaveSpore(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->kuanTi->getSpores());
    }

    private function thenKuanTiShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->player2->getHealthPoint());
    }
}
