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

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PARTICIPATE_RESEARCH->value]);
        $this->participateAction = $I->grabService(ParticipateResearch::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldBeExecutableIfRequirementsAreMet(FunctionalTester $I): void
    {
        $this->givenChunIsInLab();

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedOnLabTerminal($terminal);

        $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN);

        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $terminal,
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

        $terminal = $this->givenLabTerminal();

        $this->givenKuanTiIsFocusedOnLabTerminal($terminal);

        $project = $this->daedalus->getProjectByName(ProjectName::CREATE_MYCOSCAN);

        $this->participateAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $terminal,
            player: $this->kuanTi,
            target: $project
        );

        $this->participateAction->execute();

        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::REQUIREMENTS_NOT_MET,
            actual: $this->participateAction->cannotExecuteReason(),
        );
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

    private function givenLabTerminal()
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime()
        );
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
}
