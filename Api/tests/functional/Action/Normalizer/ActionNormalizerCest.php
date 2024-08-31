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
}
