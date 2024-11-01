<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ParticipateResearch;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
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
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $terminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PARTICIPATE_RESEARCH->value]);
        $this->participateAction = $I->grabService(ParticipateResearch::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->actionConfig->setDirtyRate(0);
        $this->terminal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    public function shouldBeExecutableIfRequirementsAreMet(FunctionalTester $I): void
    {
        $this->givenChunIsInLab();

        $this->givenKuanTiIsFocusedOnLabTerminal($this->terminal);

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

    public function shouldNotBeExecutableIfRequirementsAreNotMet(FunctionalTester $I): void
    {
        $this->givenChunIsNotInLab();

        $this->givenKuanTiIsFocusedOnLabTerminal($this->terminal);

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

    public function playerWithGeniusIdeaStatusShouldLoseStatusAfterParticipating(FunctionalTester $I): void
    {
        $this->givenKuanTiIsFocusedOnLabTerminal($this->terminal);

        $this->givenKuanTiHasGeniusIdeaStatus();

        $this->whenKuanTiParticipatesToResearch(ProjectName::ANABOLICS);

        $this->thenKuanTiDoesNotHaveGeniusIdeaStatus($I);
    }

    private function givenChunIsNotInLab()
    {
        $laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        if ($laboratory->isChunIn()) {
            $laboratory->removePlayer($this->chun);
        }

        $this->chun->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET));
    }

    private function givenChunIsInLab()
    {
        $this->chun->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        if (!$this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->isChunIn()) {
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->addPlayer($this->chun);
        }
    }

    private function givenKuanTiIsFocusedOnLabTerminal(GameEquipment $terminal): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $terminal,
        );
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

    private function thenKuanTiDoesNotHaveGeniusIdeaStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::GENIUS_IDEA));
    }
}
