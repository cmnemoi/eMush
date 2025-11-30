<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\UpgradeNeron;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class UpgradeNeronCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private NeronVersionRepositoryInterface $neronVersionRepository;
    private StatusServiceInterface $statusService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private XylophRepositoryInterface $xylophRepository;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    private GameEquipment $commsCenter;

    private ActionConfig $actionConfig;
    private UpgradeNeron $upgradeNeron;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->neronVersionRepository = $I->grabService(NeronVersionRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::UPGRADE_NERON->toString()]);
        $this->upgradeNeron = $I->grabService(UpgradeNeron::class);

        // setup projects which do not need specific room to exist to avoid errors in tests
        $this->daedalus
            ->getAllAvailableProjects()
            ->filter(static fn (Project $project) => !\in_array($project->getName(), [ProjectName::FIRE_SENSOR->toString(), ProjectName::DOOR_SENSOR->toString()], true))
            ->map(static fn (Project $project) => $project->unpropose());

        $this->givenACommsCenterInChunRoom();
        $this->givenChunIsFocusedOnCommsCenter();
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->givenChunIsNotFocusedOnCommsCenter();

        $this->whenChunTriesToUpgradeNeron();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableForNonCommsManager(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->whenChunTriesToUpgradeNeron();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::COMS_NOT_OFFICER, $I);
    }

    public function shouldNotBeExecutableIfLinkWithSolIsNotEstablished(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->givenChunIsCommsManager();
        $this->givenChunIsDirty();

        $this->whenChunTriesToUpgradeNeron();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DIRTY_RESTRICTION, $I);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->givenChunIsCommsManager();

        $this->whenChunTriesToUpgradeNeron();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED, $I);
    }

    public function shouldNotBeExecutableIfThereIsNoMoreAvailableNeronProjects(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->givenChunIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenThereIsNoneAvailableNeronProjects();

        $this->whenChunTriesToUpgradeNeron();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::MAX_NERON_VERSION_REACHED, $I);
    }

    public function shouldFinishANeronProjectOnSuccess(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->givenChunIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenNeronMinorVersionIs(99);

        $this->whenChunUpgradesNeron();

        $this->thenIShouldSeeAFinishedNeronProject($I);
    }

    public function shouldPrintPublicLogOnSuccess(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus();
        $this->givenChunIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenNeronMinorVersionIs(99);

        $this->whenChunUpgradesNeron();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** achève la mise à jour. La version majeure de NERON a augmenté. Le surplus de CPU généré par la joie de NERON a été injecté dans un projet.',
            actualRoomLogDto: new RoomLogDto($this->chun, ActionLogEnum::UPGRADE_NERON_SUCCESS, VisibilityEnum::PUBLIC, inPlayerRoom: false),
            I: $I,
        );
    }

    public function shouldIncrementCommunicationExpertStatisticWhenXylophAndRebelBasesAreCompleted(FunctionalTester $I): void
    {
        $this->createNeronVersionForDaedalus(major: 4, minor: 99);
        $this->givenChunIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsCompleted(RebelBaseEnum::WOLF, $I);
        $this->givenXylophIsUnlocked(XylophEnum::KIVANC, $I);

        $this->whenChunUpgradesNeron();
        $this->whenChunUpgradesNeron();

        $this->thenCommunicationExpertPendingStatisticShouldBe(1, $I);
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::COMMUNICATIONS_EXPERT));
    }

    private function whenChunTriesToUpgradeNeron(): void
    {
        $this->upgradeNeron->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->chun,
            target: $this->commsCenter,
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I)
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->upgradeNeron->cannotExecuteReason(),
            message: "Action should not be executable with message: {$message}, but got: {$this->upgradeNeron->cannotExecuteReason()}",
        );
    }

    private function givenACommsCenterInChunRoom(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsNotFocusedOnCommsCenter(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter,
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse(
            condition: $this->upgradeNeron->isVisible(),
            message: 'Action should not be visible',
        );
    }

    private function givenChunIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsCommsManager(): void
    {
        $this->chun->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenThereIsNoneAvailableNeronProjects(): void
    {
        $this->daedalus
            ->getAvailableNeronProjects()
            ->map(static fn (Project $project) => $project->unpropose());
    }

    private function givenLinkWithSolIsEstablished(): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $linkWithSol->establish();
    }

    private function givenNeronMinorVersionIs(int $minor): void
    {
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $neronVersion->increment($minor);
    }

    private function createNeronVersionForDaedalus(int $major = 1, int $minor = 0): void
    {
        $this->neronVersionRepository->save(new NeronVersion($this->daedalus->getId(), $major, $minor));
    }

    private function thenIShouldSeeAFinishedNeronProject(FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->daedalus->getFinishedNeronProjects(),
            message: 'There should be 1 finished neron project, found: ' . \count($this->daedalus->getFinishedNeronProjects()),
        );
    }

    private function givenRebelBaseIsCompleted(RebelBaseEnum $rebelBaseEnum, FunctionalTester $I): void
    {
        $rebeBase = new RebelBase(
            config: $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseEnum->value]),
            daedalusId: $this->daedalus->getId(),
        );
        $rebeBase->increaseDecodingProgress(100);
        $this->rebelBaseRepository->save($rebeBase);
    }

    private function givenXylophIsUnlocked(XylophEnum $xylophEnum, FunctionalTester $I): void
    {
        $xyloph = new XylophEntry(
            xylophConfig: $I->grabEntityFromRepository(XylophConfig::class, ['name' => $xylophEnum->value]),
            daedalusId: $this->daedalus->getId(),
        );
        $xyloph->unlockDatabase();
        $this->xylophRepository->save($xyloph);
    }

    private function thenCommunicationExpertPendingStatisticShouldBe(int $expected, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $expected,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                name: StatisticEnum::COMMUNICATION_EXPERT,
                userId: $this->player->getUser()->getId(),
                closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getCount(),
        );
    }

    private function whenChunUpgradesNeron(): void
    {
        $this->whenChunTriesToUpgradeNeron();
        $this->upgradeNeron->execute();
    }
}
