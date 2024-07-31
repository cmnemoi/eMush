<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Normalizer;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Status\Entity\ChargeStatus;
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

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->normalizer = $I->grabService(ActionNormalizer::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNormalizeTrapClosetAction(FunctionalTester $I): void
    {
        // given KT is Mush so he has the action available
        $mushStatus = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

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
                    'id' => $mushStatus->getId(),
                    'class' => ChargeStatus::class,
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
}
