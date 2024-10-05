<?php

declare(strict_types=1);

namespace Mush\tests\functional\Status\Normalizer;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StatusNormalizerCest extends AbstractFunctionalTest
{
    private StatusNormalizer $statusNormalizer;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->statusNormalizer = $I->grabService(StatusNormalizer::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNormalizeStatus(FunctionalTester $I): void
    {
        $status = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            visibility: VisibilityEnum::PUBLIC,
        );

        $normalizedStatus = $this->statusNormalizer->normalize(
            object: $status,
            format: null,
            context: ['currentPlayer' => $this->chun],
        );

        $I->assertEquals(
            expected: [
                'key' => PlayerStatusEnum::LYING_DOWN,
                'name' => 'Allongée',
                'description' => 'Vous êtes allongée.',
                'isPrivate' => false,
            ],
            actual: $normalizedStatus,
        );
    }
}
