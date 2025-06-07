<?php

namespace Mush\tests\functional\Daedalus\Normalizer;

use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Daedalus\Normalizer\ClosedDaedalusNormalizer;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class ClosedDaedalusNormalizerCest extends AbstractFunctionalTest
{
    private ClosedDaedalusNormalizer $normalizer;
    private DaedalusService $daedalusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(ClosedDaedalusNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function shouldNormalizeDaedalusStatisticsCorrectly(FunctionalTester $I): void
    {
        $finishedDaedalus = $this->createDaedalus($I);
        $daedalusStatistics = new DaedalusStatistics(planetsFound: 1, explorationsStarted: 2, shipsDestroyed: 5, rebelBasesContacted: 1, sporesCreated: 4, mushAmount: 2);

        $finishedDaedalus->getDaedalusInfo()->setDaedalusStatistics($daedalusStatistics);

        $closedDaedalus = $this->daedalusService->endDaedalus($finishedDaedalus, 'super_nova', new \DateTime());

        // when i normalize
        $normalizedDaedalus = $this->normalizer->normalize($closedDaedalus);

        $I->assertEquals(
            expected: [
                [
                    'name' => 'Planètes trouvées',
                    'value' => 1,
                ],
                [
                    'name' => 'Explorations',
                    'value' => 2,
                ],
                [
                    'name' => 'Vaisseaux détruits',
                    'value' => 5,
                ],
                [
                    'name' => 'Spores générés',
                    'value' => 4,
                ],
                [
                    'name' => 'Nombre de Mush',
                    'value' => 2,
                ],
                [
                    'name' => 'Bases rebelles contactées',
                    'value' => 1,
                ],
            ],
            actual: $normalizedDaedalus['statistics']['lines']
        );
    }
}
