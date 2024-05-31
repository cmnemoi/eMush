<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Status\Normalizer;

use Mush\Game\Enum\SkillEnum;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StatusNormalizerCest extends AbstractFunctionalTest
{
    private StatusNormalizer $normalizer;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->normalizer = $I->grabService(StatusNormalizer::class);

        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNormalizeShrinkSkill(FunctionalTester $I): void
    {
        $status = $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        $normalizedStatus = $this->normalizer->normalize($status, format: null, context: ['currentPlayer' => $this->chun]);

        $I->assertEquals(
            expected: [
                'key' => SkillEnum::SHRINK,
                'name' => 'Psy',
                'description' => 'Le psy occupe un poste de soutien psychologique. Il permet de garder le moral et soigne les maladies psychologiques.//:point: A chaque cycle, **1 Point de Moral (:pmo:) est régénéré** à chaque personnage allongé dans sa pièce.//:point: **Soigne les maladies Psy**.//:point: Accorde l\'action **Réconforter**, laquelle améliore le moral.//:point: Bonus pour développer certains **Projets NERON**.',
                'isPrivate' => false,
            ],
            actual: $normalizedStatus,
        );
    }
}
