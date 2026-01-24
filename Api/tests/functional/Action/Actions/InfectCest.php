<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Entity\PendingStatistic;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class InfectCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Infect $infect;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private PlayerDiseaseService $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INFECT->value]);
        $this->infect = $I->grabService(Infect::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseService::class);

        $this->givenKuanTiIsMush();
    }

    public function infectorShouldBeAbleToInfectTwiceADay(FunctionalTester $I)
    {
        $this->addSkillToPlayer(SkillEnum::INFECTOR, $I, $this->kuanTi);

        $this->givenKuanTiInfectsPlayer();

        $this->whenKuanTiTriesToInfect();

        $this->thenActionShouldBeExecutable($I);
    }

    public function dayChangeShouldMakeAbleToInfectAgain(FunctionalTester $I): void
    {
        $this->givenKuanTiInfectsPlayer();

        $this->whenANewDayPasses();

        $this->thenActionShouldBeExecutable($I);
    }

    public function mushAllergyShouldRemoveHealthPointsToTargetPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasMushAllergy();

        $this->givenPlayerHasHealthPoints(10);

        $this->whenKuanTiInfectsPlayer();

        $this->thenPlayerShouldHaveHealthPoints(6, $I);
    }

    public function mushovoreBacteriaShouldPreventPlayersToTurnMushAtThreeSpores(FunctionalTester $I): void
    {
        $this->givenMushovoreBacteriaIsCompleted($I);

        $this->givenPlayerHasSpores(2);

        $this->whenKuanTiInfectsPlayer();

        $this->thenPlayerShouldBeHuman($I);
    }

    public function shouldMakeMycoAlarmRing(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->whenKuanTiInfectsPlayer();

        $this->thenMycoAlarmPrintsPublicLog($I);
    }

    public function shouldIncrementPendingStatistic(FunctionalTester $I): void
    {
        $this->givenPlayerHasSpores(2);

        $this->whenKuanTiInfectsPlayer();

        $this->thenKuanTiShouldHaveContaminatorStatistic($I);
        $this->thenPlayerShouldHaveInfectedStatistic($I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->eventService->callEvent(
            event: new PlayerEvent(
                player: $this->kuanTi,
                tags: [],
                time: new \DateTime(),
            ),
            name: PlayerEvent::CONVERSION_PLAYER,
        );
        $this->kuanTi->setSpores(2);
    }

    private function givenKuanTiInfectsPlayer(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
        $this->infect->execute();
    }

    private function givenPlayerHasMushAllergy(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::MUSH_ALLERGY->toString(),
            player: $this->player,
            reasons: [],
        );
    }

    private function givenPlayerHasHealthPoints(int $healthPoints): void
    {
        $this->player->setHealthPoint($healthPoints);
    }

    private function givenMushovoreBacteriaIsCompleted(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::MUSHOVORE_BACTERIA),
            author: $this->player,
            I: $I
        );
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores($spores);
    }

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenKuanTiTriesToInfect(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
    }

    private function whenKuanTiInfectsPlayer(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
        $this->infect->execute();
    }

    private function whenANewDayPasses(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(
                player: $this->kuanTi,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            ),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE,
        );
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->infect->cannotExecuteReason());
    }

    private function thenPlayerShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->player->getHealthPoint());
    }

    private function thenPlayerShouldBeHuman(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->isHuman());
    }

    private function thenMycoAlarmPrintsPublicLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':mycoalarm: DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::MYCO_ALARM_RING,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    private function thenKuanTiShouldHaveContaminatorStatistic(FunctionalTester $I): void
    {
        $stats = $this->pendingStatisticRepository->findAllByClosedDaedalusId($this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId());
        $contaminatorStats = array_values(array_filter($stats, static fn (PendingStatistic $stat) => $stat->getConfig()->getName() === StatisticEnum::HAS_MUSHED));

        $I->assertCount(1, $contaminatorStats);

        /** @var PendingStatistic $statistic */
        $statistic = $contaminatorStats[0];

        $I->assertEquals($this->kuanTi->getUser()->getId(), $statistic->getUserId());
        $I->assertEquals(1, $statistic->getCount());
    }

    private function thenPlayerShouldHaveInfectedStatistic(FunctionalTester $I): void
    {
        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::MUSHED,
            userId: $this->player->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
        );

        $I->assertEquals(1, $statistic?->getCount());
    }
}
