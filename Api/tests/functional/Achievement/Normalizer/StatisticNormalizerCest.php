<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Normalizer;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Normalizer\StatisticNormalizer;
use Mush\Achievement\ViewModel\StatisticViewModel;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StatisticNormalizerCest extends AbstractFunctionalTest
{
    private StatisticNormalizer $statisticNormalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statisticNormalizer = $I->grabService(StatisticNormalizer::class);
    }

    public function testShouldNormalizeAchievement(FunctionalTester $I): void
    {
        $statistic = new StatisticViewModel(
            key: StatisticEnum::PLANET_SCANNED->value,
            count: 1,
            isRare: false,
        );

        $normalizedStatistic = $this->statisticNormalizer->normalize($statistic, format: null, context: ['language' => LanguageEnum::FRENCH]);

        $I->assertEquals(
            expected: [
                'key' => 'planet_scanned',
                'name' => 'Planètes détectées',
                'description' => 'Nombre de planètes que vous avez découvertes.',
                'isRare' => false,
                'count' => 1,
                'formattedCount' => 'x1',
            ],
            actual: $normalizedStatistic
        );
    }
}
