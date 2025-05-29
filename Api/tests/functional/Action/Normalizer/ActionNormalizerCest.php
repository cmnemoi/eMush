<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Normalizer;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionNormalizerCest extends AbstractFunctionalTest
{
    private ActionNormalizer $normalizer;

    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->normalizer = $I->grabService(ActionNormalizer::class);
        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNormalizeTrapClosetAction(FunctionalTester $I): void
    {
        // given KT is Mush and has Trapper skill so he has the action available
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->addSkillToPlayer->execute(SkillEnum::TRAPPER, $this->kuanTi);

        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // when I normalize trap closet action for KT
        /** @var Action $trapClosetAction */
        $trapClosetAction = $this->kuanTi
            ->getProvidedActions(ActionHolderEnum::PLAYER, [ActionRangeEnum::SELF])
            ->filter(static fn (Action $action) => $action->getActionConfig()->getActionName() === ActionEnum::TRAP_CLOSET)
            ->first();

        $normalizedAction = $this->normalizer->normalize($trapClosetAction, format: null, context: ['currentPlayer' => $this->kuanTi]);

        // then I should see the normalized action
        $I->assertEquals(
            expected: [
                'id' => $trapClosetAction->getActionConfig()->getId(),
                'key' => 'trap_closet',
                'actionProvider' => [
                    'id' => $this->kuanTi->getSkillByNameOrThrow(SkillEnum::TRAPPER)->getId(),
                    'class' => Skill::class,
                ],
                'actionPointCost' => 1,
                'moralPointCost' => 0,
                'movementPointCost' => 0,
                'skillPointCosts' => [],
                'successRate' => 100,
                'name' => 'Piéger pièce',
                'description' => 'Permet de piéger la pièce, toutes les interactions avec les objets et équipements de la pièce déclencheront le piège.//Cette action est **Discrète**. Elle sera révélée par les **Caméras** et les **Équipiers**, y compris ceux de **votre camp**.',
                'canExecute' => true,
                'confirmation' => null,
            ],
            actual: $normalizedAction,
        );
    }

    public function shouldNormalizeExtractSporeAction(FunctionalTester $I): void
    {
        // given KT is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // when I normalize extract spore action for KT
        /** @var Action $trapClosetAction */
        $extractSporeAction = $this->kuanTi
            ->getProvidedActions(ActionHolderEnum::PLAYER, [ActionRangeEnum::SELF])
            ->filter(static fn (Action $action) => $action->getActionConfig()->getActionName() === ActionEnum::EXTRACT_SPORE)
            ->first();

        $normalizedAction = $this->normalizer->normalize($extractSporeAction, format: null, context: ['currentPlayer' => $this->kuanTi]);

        // then I should see the normalized action
        $I->assertEquals(
            expected: [
                'id' => $extractSporeAction->getActionConfig()->getId(),
                'key' => 'extract_spore',
                'actionProvider' => [
                    'id' => $this->kuanTi->getId(),
                    'class' => Player::class,
                ],
                'actionPointCost' => 2,
                'moralPointCost' => 0,
                'movementPointCost' => 0,
                'skillPointCosts' => [],
                'successRate' => 100,
                'name' => 'Extirper un spore',
                'description' => "Extirpez-vous un spore pour ensuite contaminer un coéquipier (mais avant ça, repérez les caméras).//
        L'ensemble des **Mush** peuvent encore produire **4 spores** aujourd'hui.//Cette action est **Discrète**. Elle sera révélée par les **Caméras** et les **Équipiers**, y compris ceux de **votre camp**.",
                'canExecute' => true,
                'confirmation' => null,
            ],
            actual: $normalizedAction,
        );
    }

    public function shouldNormalizeDiySurgeryAction(FunctionalTester $I): void
    {
        // given a surgery plot in the room
        $surgeryPlot = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SURGERY_PLOT,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given a bed in the room
        $bed = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BED,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun is laid down on the bed
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $bed,
        );

        // given self surgery action
        $action = $surgeryPlot
            ->getActions($this->chun, ActionHolderEnum::EQUIPMENT)
            ->filter(static fn (Action $action) => $action->getActionConfig()->getActionName() === ActionEnum::SELF_SURGERY)
            ->first();

        // when I normalize the action
        $normalizedAction = $this->normalizer->normalize($action, format: null, context: [
            'currentPlayer' => $this->chun,
            $surgeryPlot->getClassName() => $surgeryPlot,
        ]);

        // then I should see the normalized action
        $I->assertEquals(
            expected: [
                'id' => $action->getActionConfig()->getId(),
                'key' => 'self_surgery',
                'actionProvider' => [
                    'id' => $surgeryPlot->getId(),
                    'class' => GameEquipment::class,
                ],
                'actionPointCost' => 4,
                'moralPointCost' => 0,
                'movementPointCost' => 0,
                'skillPointCosts' => [],
                'successRate' => 100,
                'name' => 'Chirurgie-Auto',
                'description' => 'Vous n\'êtes pas blessée, mais ça peut s\'arranger...',
                'canExecute' => false,
            ],
            actual: $normalizedAction,
        );
    }

    public function shouldNormalizePrintZeListAction(FunctionalTester $I): void
    {
        // given a tabulatrix in the room
        $tabulatrix = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TABULATRIX,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given Kuan Ti is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Daedalus has been created 10 days ago
        $this->daedalus->setCreatedAt(new \DateTime('-10 days'));

        // given Chun is a tracker
        $this->addSkillToPlayer->execute(SkillEnum::TRACKER, $this->chun);

        // given print ze list action
        $action = $tabulatrix
            ->getActions($this->chun, ActionHolderEnum::EQUIPMENT)
            ->filter(static fn (Action $action) => $action->getActionConfig()->getActionName() === ActionEnum::PRINT_ZE_LIST)
            ->first();

        // when I normalize the action
        $normalizedAction = $this->normalizer->normalize($action, format: null, context: [
            'currentPlayer' => $this->chun,
            $tabulatrix->getClassName() => $tabulatrix,
        ]);

        // then I should see the normalized action
        $I->assertEquals(
            expected: [
                'id' => $action->getActionConfig()->getId(),
                'key' => 'print_ze_list',
                'actionProvider' => [
                    'id' => $tabulatrix->getId(),
                    'class' => GameEquipment::class,
                ],
                'actionPointCost' => 0,
                'moralPointCost' => 0,
                'movementPointCost' => 0,
                'skillPointCosts' => [],
                'successRate' => 100,
                'name' => 'Imprimer LA liste',
                'description' => 'Vous permet de récupérer une liste que vous avez créée avant le départ du Daedalus. Cette liste contient des noms de personnes potentiellement infectées. À chaque jour, cette liste s\'affine d\'un nom. Cette liste contient actuellement **1** nom.',
                'canExecute' => true,
                'confirmation' => null,
            ],
            actual: $normalizedAction,
        );
    }

    public function shouldShowCoreIconActionForITDesigner(FunctionalTester $I): void
    {
        $this->givenPlayerIsITExpert($I);
        $this->givenPlayerIsDesigner($I);
        $neronCore = $this->givenRoomHasNeronCore();
        $project = $this->givenThereIsProjectAvailable();
        $this->givenPlayerFocusesOn($neronCore);
        $participateAction = $this->givenParticipateActionIn($neronCore, $I);
        $normalizedAction = $this->whenActionIsNormalized($participateAction, $project);
        $this->thenPlayerShouldSeeCorePointsIcon($normalizedAction, $I);
    }

    public function shouldShowCoreIconActionForDesignerIT(FunctionalTester $I): void
    {
        $this->givenPlayerIsDesigner($I);
        $this->givenPlayerIsITExpert($I);
        $neronCore = $this->givenRoomHasNeronCore();
        $project = $this->givenThereIsProjectAvailable();
        $this->givenPlayerFocusesOn($neronCore);
        $participateAction = $this->givenParticipateActionIn($neronCore, $I);
        $normalizedAction = $this->whenActionIsNormalized($participateAction, $project);
        $this->thenPlayerShouldSeeCorePointsIcon($normalizedAction, $I);
    }

    private function givenRoomHasNeronCore(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerFocusesOn(GameEquipment $terminal): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $terminal,
        );
    }

    private function givenThereIsProjectAvailable(): Project
    {
        $project = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $project->propose();

        return $project;
    }

    private function givenParticipateActionIn(GameEquipment $terminal): Action
    {
        return $terminal
            ->getActions($this->player, ActionHolderEnum::PROJECT)
            ->first();
    }

    private function givenPlayerIsITExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I, $this->player);
    }

    private function givenPlayerIsDesigner(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::CONCEPTOR, $I, $this->player);
    }

    private function whenActionIsNormalized(Action $action, Project $project): array
    {
        return $this->normalizer->normalize($action, format: null, context: [
            'currentPlayer' => $this->player,
            $project->getClassName() => $project,
        ]);
    }

    private function thenPlayerShouldSeeCorePointsIcon(array $normalizedAction, FunctionalTester $I): void
    {
        $skillPointCosts = $normalizedAction['skillPointCosts'];
        $I->assertEquals('core', $skillPointCosts[0], "Expected core icon, got {$skillPointCosts[0]}");
    }
}
