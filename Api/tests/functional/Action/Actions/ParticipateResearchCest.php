<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ParticipateResearch;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Communications\Service\DecodeXylophDatabaseServiceInterface;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Service\NeronServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\ValueObject\PlayerEfficiency;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ParticipateResearchCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ParticipateResearch $participateAction;
    private DecodeXylophDatabaseServiceInterface $decodeXylophDatabaseService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private NeronServiceInterface $neronService;
    private StatusServiceInterface $statusService;
    private XylophRepositoryInterface $xylophRepository;
    private GameEquipment $terminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PARTICIPATE_RESEARCH->value]);
        $this->participateAction = $I->grabService(ParticipateResearch::class);
        $this->decodeXylophDatabaseService = $I->grabService(DecodeXylophDatabaseServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->neronService = $I->grabService(NeronServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);
        $this->actionConfig->setDirtyRate(0);
        $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);

        $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedOnLabTerminal();
    }

    public function shouldBeExecutableIfRequirementsAreMet(FunctionalTester $I): void
    {
        $this->givenChunIsInLab();

        $this->givenGameHasStarted();

        $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN);

        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $project
        );

        $this->participateAction->execute();

        $I->assertEquals(
            expected: null,
            actual: $this->participateAction->cannotExecuteReason(),
        );
    }

    public function shouldNotBeExecutableIfGameStartedRequirementIsNotMet(FunctionalTester $I): void
    {
        $this->givenChunIsInLab();

        $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN);

        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $project
        );

        $this->participateAction->execute();

        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::REQUIREMENTS_NOT_MET,
            actual: $this->participateAction->cannotExecuteReason(),
        );

        $I->assertEquals(
            $this->daedalus->getDaedalusInfo()->getGameStatus(),
            GameStatusEnum::STANDBY
        );
    }

    public function shouldNotBeExecutableIfChunRequirementIsNotMet(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();

        $this->givenGameHasStarted();

        $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN);

        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $project
        );

        $this->participateAction->execute();

        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::REQUIREMENTS_NOT_MET,
            actual: $this->participateAction->cannotExecuteReason(),
        );
    }

    public function shouldNotBeExecutableIfFoodRequirementIsNotMet(FunctionalTester $I): void
    {
        $project = $this->daedalus->getProjectByName(ProjectName::CONSTIPASPORE_SERUM);

        $this->givenGameHasStarted();

        $this->givenMushSampleInLaboratory();

        $this->whenKuanTiTriesToParticipateInProject($project);

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::REQUIREMENTS_NOT_MET);
    }

    public function shouldBeExecutableIfFoodRequirementIsMet(FunctionalTester $I): void
    {
        $project = $this->daedalus->getProjectByName(ProjectName::CONSTIPASPORE_SERUM);

        $this->givenGameHasStarted();

        $this->givenMushSampleInLaboratory();

        $this->givenABananaInLaboratory();

        $this->whenKuanTiTriesToParticipateInProject($project);

        $this->thenActionIsExecutable($I);
    }

    public function playerWithGeniusIdeaStatusShouldLoseStatusAfterParticipating(FunctionalTester $I): void
    {
        $this->givenKuanTiHasGeniusIdeaStatus();

        $this->whenKuanTiParticipatesToResearch(ProjectName::ANABOLICS);

        $this->thenKuanTiDoesNotHaveGeniusIdeaStatus($I);
    }

    public function shouldPutEfficiencyToFiveSevenPercentsWithCpuPriority(FunctionalTester $I): void
    {
        $reasearchProject = $this->daedalus->getProjectByName(ProjectName::RETRO_FUNGAL_SERUM);

        // given CPU priority is set to research
        $this->neronService->changeCpuPriority(
            neron: $this->daedalus->getNeron(),
            cpuPriority: NeronCpuPriorityEnum::RESEARCH,
        );

        // when Chun participates in the project with CPU priority
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $reasearchProject
        );
        $this->participateAction->execute();

        // then Chun's efficiency should be reduced 2-3%
        $I->assertEquals(new PlayerEfficiency(2, 3), $this->chun->getEfficiencyForProject($reasearchProject));
    }

    public function shouldPutEfficiencyToFourPercentsWithPrintedCircuitJellyInNexus(FunctionalTester $I): void
    {
        $researchProject = $this->daedalus->getProjectByName(ProjectName::RETRO_FUNGAL_SERUM);

        $this->givenPrintedCircuitJellyInNexus();

        // when Chun participates in the project with Printed Circuit Jelly in Nexus
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $researchProject
        );
        $this->participateAction->execute();

        // then Chun's efficiency should be increased to 4-4%
        $I->assertEquals(new PlayerEfficiency(4, 4), $this->chun->getEfficiencyForProject($researchProject));
    }

    public function shouldPutEfficiencyToFiveSixPercentsWithCpuPriorityAndPrintedCircuitJellyInNexus(FunctionalTester $I): void
    {
        $researchProject = $this->daedalus->getProjectByName(ProjectName::RETRO_FUNGAL_SERUM);

        $this->givenPrintedCircuitJellyInNexus();

        // given CPU priority is set to research
        $this->neronService->changeCpuPriority(
            neron: $this->daedalus->getNeron(),
            cpuPriority: NeronCpuPriorityEnum::RESEARCH,
        );

        // when Chun participates in the project with CPU priority and Printed Circuit Jelly in Nexus
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->chun,
            target: $researchProject
        );
        $this->participateAction->execute();

        // then Chun's efficiency should be increased to 5-6%
        $I->assertEquals(new PlayerEfficiency(5, 6), $this->chun->getEfficiencyForProject($researchProject));
    }

    public function shouldBeExecutableWithGhostSample(FunctionalTester $I): void
    {
        $this->givenGameHasStarted();

        $this->givenGhostSampleTakesEffect($I);

        $project = $this->daedalus->getProjectByName(ProjectName::ANTISPORE_GAS);

        $this->whenKuanTiTriesToParticipateInProject($project);

        $this->thenActionIsExecutable($I);
    }

    public function shouldBeExecutableWithGhostSampleWhenMushSampleIsTaken(FunctionalTester $I): void
    {
        $this->givenGameHasStarted();

        $this->givenGhostSampleTakesEffect($I);

        $mushSample = $this->givenMushSampleInLaboratory();

        $project = $this->daedalus->getProjectByName(ProjectName::ANTISPORE_GAS);

        $this->whenChunTakes($mushSample, $I);

        $this->givenChunIsNotInLab();

        $this->whenKuanTiTriesToParticipateInProject($project);

        $this->thenActionIsExecutable($I);
    }

    public function shouldBeExecutableWithGhostChunWhenChunIsNotThere(FunctionalTester $I): void
    {
        $this->givenGameHasStarted();

        $this->givenGhostChunTakesEffect($I);

        $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN);

        $this->givenChunIsNotInLab();

        $this->whenKuanTiTriesToParticipateInProject($project);

        $this->thenActionIsExecutable($I);
    }

    private function givenChunIsNotInLab()
    {
        $laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        if ($laboratory->isChunIn()) {
            $laboratory->removePlayer($this->chun);
        }

        $this->chun->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET));
    }

    private function givenChunIsInLab(): void
    {
        $this->chun->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        if (!$this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->isChunIn()) {
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->addPlayer($this->chun);
        }
    }

    private function givenGameHasStarted(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
    }

    private function givenLabTerminal(): void
    {
        $this->terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenKuanTiIsFocusedOnLabTerminal(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $this->terminal,
        );
    }

    private function givenMushSampleInLaboratory(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MUSH_SAMPLE,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenABananaInLaboratory(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::BANANA,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenGhostSampleTakesEffect(FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(XylophConfig::class, ['key' => XylophEnum::GHOST_SAMPLE->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $this->xylophRepository->save($xylophEntry);

        $this->decodeXylophDatabaseService->execute(
            xylophEntry: $xylophEntry,
            player: $this->player,
        );
    }

    private function givenGhostChunTakesEffect(FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(XylophConfig::class, ['key' => XylophEnum::GHOST_CHUN->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $this->xylophRepository->save($xylophEntry);

        $this->decodeXylophDatabaseService->execute(
            xylophEntry: $xylophEntry,
            player: $this->player,
        );
    }

    private function whenKuanTiTriesToParticipateInProject(Project $project): void
    {
        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $project
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($message, $this->participateAction->cannotExecuteReason());
    }

    private function thenActionIsExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->participateAction->cannotExecuteReason());
    }

    private function givenKuanTiHasGeniusIdeaStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GENIUS_IDEA,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenKuanTiParticipatesToResearch(ProjectName $projectName): void
    {
        $project = $this->daedalus->getProjectByName($projectName);

        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->terminal,
            player: $this->kuanTi,
            target: $project
        );
        $this->participateAction->execute();
    }

    private function whenChunTakes(GameItem $item, FunctionalTester $I): void
    {
        $takeAction = $I->grabService(Take::class);
        $takeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TAKE->value]);
        $takeAction->loadParameters(
            actionConfig: $takeConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $item
        );
        $takeAction->execute();
    }

    private function thenKuanTiDoesNotHaveGeniusIdeaStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::GENIUS_IDEA));
    }

    private function givenPrintedCircuitJellyInNexus(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PRINTED_CIRCUIT_JELLY,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::NEXUS),
            reasons: [],
            time: new \DateTime()
        );
    }
}
