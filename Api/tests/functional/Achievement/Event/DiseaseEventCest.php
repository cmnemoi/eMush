<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Service;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Heal;
use Mush\Action\Actions\SelfHeal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DiseaseEventCest extends AbstractFunctionalTest
{
    private ActionConfig $healConfig;
    private Heal $healAction;

    private ActionConfig $selfHealConfig;
    private SelfHeal $selfHealAction;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->healConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HEAL]);
        $this->healAction = $I->grabService(Heal::class);

        $this->selfHealConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SELF_HEAL]);
        $this->selfHealAction = $I->grabService(SelfHeal::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->daedalus->getGameConfig()->getDifficultyConfig()->setCycleDiseaseRate(0);
    }

    public function shouldIncrementDiseaseContractedPendingStatisticWhenGettingIll(FunctionalTester $I): void
    {
        $this->givenPlayerHasDiseaseAppearByName(DiseaseEnum::FLU);

        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);

        $this->whenCycleIsProgressedForPlayer();

        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);
    }

    public function shouldIncrementDiseaseContractedPendingStatisticWhenGettingIllAfterDelay(FunctionalTester $I): void
    {
        $this->givenPlayerGetsDiseaseByNameWithDelay(DiseaseEnum::FLU, 2);
        // 2 cycles until the disease appears
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);

        $this->whenCycleIsProgressedForPlayer();
        // 1 cycle until the disease appears
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);

        $this->whenCycleIsProgressedForPlayer();
        // the disease has appeared
        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);
    }

    public function shouldNotIncrementDiseaseContractedPendingStatisticWhenGettingDisorder(FunctionalTester $I): void
    {
        $this->givenPlayerGetsDiseaseByNameWithDelay(DisorderEnum::SPLEEN, 1);

        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);
        $this->thenPatientHasActiveDiseasesOfAmount(0, $I);

        $this->whenCycleIsProgressedForPlayer();

        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::DISEASE_CONTRACTED, $this->player, $I);
        $this->thenPatientHasActiveDiseasesOfAmount(1, $I);
    }

    public function shouldIncrementShrinkerPendingStatisticWhenCuringDisorder(FunctionalTester $I): void
    {
        // player2 is shrink
        $this->givenPlayerLiesDownWithShrinkInTheRoom($I);
        $this->givenPlayerHasActiveDiseaseByNameWithDiseasePoints(DisorderEnum::PARANOIA, 2);

        $this->whenCycleIsProgressedForPlayer();
        // Paranoia has 1 disease point
        $this->thenPatientHasActiveDiseasesOfAmount(1, $I);
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::SHRINKER, $this->player2, $I);

        $this->whenCycleIsProgressedForPlayer();
        // Paranoia has no disease points and is cured
        $this->thenPatientHasActiveDiseasesOfAmount(0, $I);
        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::SHRINKER, $this->player2, $I);
    }

    public function shouldNotIncrementShrinkerPendingStatisticWhenDisorderSelfCured(FunctionalTester $I): void
    {
        $this->givenPlayerHasActiveDiseaseByNameWithDiseasePoints(DisorderEnum::SPLEEN, 1);

        $this->thenPatientHasActiveDiseasesOfAmount(1, $I);

        $this->whenCycleIsProgressedForPlayer();

        $this->thenPatientHasActiveDiseasesOfAmount(0, $I);
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::SHRINKER, $this->player, $I);
    }

    public function shouldIncrementPhysicianPendingStatisticWhenCuringDisease(FunctionalTester $I): void
    {
        $this->givenPlayerHasActiveDiseaseByNameWithResistancePoints(DiseaseEnum::BLACK_BITE, 1);
        $this->givenOtherPlayerHasMedikit();
        $this->givenPlayerIsLowHealth();

        $this->whenPlayerIsHealed();

        // Black bite had a resistance point which has been removed but the disease still stays
        $this->thenPatientHasActiveDiseasesOfAmount(1, $I);
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::PHYSICIAN, $this->player2, $I);

        $this->whenPlayerIsHealed();
        // Black bite had no resistance points so is cured
        $this->thenPatientHasActiveDiseasesOfAmount(0, $I);
        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::PHYSICIAN, $this->player2, $I);
    }

    public function shouldIncrementPhysicianPendingStatisticWhenSelfCuringDisease(FunctionalTester $I): void
    {
        $this->givenPlayerHasActiveDiseaseByNameWithResistancePoints(DiseaseEnum::BLACK_BITE, 1);
        $this->givenPlayerHasMedikit();
        $this->givenPlayerIsLowHealth();

        $this->whenPlayerSelfHeals();

        // Black bite had a resistance point which has been removed but the disease still stays
        $this->thenPatientHasActiveDiseasesOfAmount(1, $I);
        $this->thenPlayerShouldNotHavePendingStatistic(StatisticEnum::PHYSICIAN, $this->player, $I);

        $this->whenPlayerSelfHeals();
        // Black bite had no resistance points so is cured
        $this->thenPatientHasActiveDiseasesOfAmount(0, $I);
        $this->thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum::PHYSICIAN, $this->player, $I);
    }

    private function givenPlayerLiesDownWithShrinkInTheRoom(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(PlayerStatusEnum::LYING_DOWN, $this->player, [], new \DateTime());
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I, $this->player2);
    }

    private function givenPlayerHasDiseaseAppearByName(string $diseaseName): PlayerDisease
    {
        return $this->givenPlayerGetsDiseaseByNameWithDelay($diseaseName, 0);
    }

    private function givenPlayerGetsDiseaseByNameWithDelay(string $diseaseName, int $delay): PlayerDisease
    {
        return $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $diseaseName,
            player: $this->player,
            reasons: ['test'],
            delayMin: $delay,
            delayLength: 0,
        );
    }

    private function givenPlayerHasActiveDiseaseByNameWithDiseasePoints(string $diseaseName, int $diseasePoints): void
    {
        $this->givenPlayerHasDiseaseAppearByName($diseaseName)->setDiseasePoint($diseasePoints);
    }

    private function givenPlayerHasActiveDiseaseByNameWithResistancePoints(string $diseaseName, int $resistancePoints): void
    {
        $this->givenPlayerHasDiseaseAppearByName($diseaseName)->setResistancePoint($resistancePoints);
    }

    private function givenOtherPlayerHasMedikit(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::MEDIKIT,
            $this->player2,
            ['test'],
            new \DateTime()
        );
    }

    private function givenPlayerHasMedikit(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::MEDIKIT,
            $this->player,
            ['test'],
            new \DateTime()
        );
    }

    private function givenPlayerIsLowHealth(): void
    {
        $this->player->setHealthPoint(1);
    }

    private function whenCycleIsProgressedForPlayer(): void
    {
        $cycleEvent = new PlayerCycleEvent($this->player, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function whenPlayerIsHealed(): void
    {
        $this->healAction->loadParameters(
            $this->healConfig,
            $this->player2,
            $this->player2,
            $this->player
        );
        $this->healAction->execute();
    }

    private function whenPlayerSelfHeals(): void
    {
        $this->selfHealAction->loadParameters(
            $this->selfHealConfig,
            $this->player,
            $this->player
        );
        $this->selfHealAction->execute();
    }

    private function thenPlayerShouldHaveOnePointOfPendingStatistic(StatisticEnum $statisticName, Player $player, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: $statisticName,
            userId: $player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
        );
        $I->assertEquals(1, $pendingStatistic?->getCount());
    }

    private function thenPlayerShouldNotHavePendingStatistic(StatisticEnum $statisticName, Player $player, FunctionalTester $I): void
    {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: $statisticName,
            userId: $player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
        );
        $I->assertNull($pendingStatistic);
    }

    private function thenPatientHasActiveDiseasesOfAmount(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertEquals($expectedCount, $this->player->getMedicalConditions()->getActiveDiseases()->count());
    }
}
