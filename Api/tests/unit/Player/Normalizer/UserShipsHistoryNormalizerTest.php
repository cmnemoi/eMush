<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Normalizer\UserShipsHistoryNormalizer;
use Mush\Player\ViewModel\UserShipsHistoryViewModel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UserShipsHistoryNormalizerTest extends TestCase
{
    private UserShipsHistoryNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new UserShipsHistoryNormalizer(
            $this->stubTranslationService(
                translations: [
                    'jin_su.name' => 'Jin Su',
                    'assassinated.name' => 'Assassiné',
                ]
            )
        );
    }

    public function testShouldNormalizeUserShipsHistoryViewModel(): void
    {
        // given
        $viewModel = new UserShipsHistoryViewModel(
            characterName: 'jin_su',
            daysSurvived: 10,
            nbExplorations: 5,
            nbNeronProjects: 3,
            nbResearchProjects: 2,
            nbScannedPlanets: 7,
            titles: ['commander', 'neron_manager'],
            triumph: 150,
            endCause: 'assassinated',
            daedalusId: 42,
            playerWasMush: false,
        );

        // when
        $normalized = $this->normalizer->normalize($viewModel, null, ['language' => 'fr']);

        // then
        self::assertEquals([
            'characterName' => ':jin_su: Jin Su',
            'daysSurvived' => 10,
            'nbExplorations' => 5,
            'nbNeronProjects' => 3,
            'nbResearchProjects' => 2,
            'nbScannedPlanets' => 7,
            'titles' => ':commander::neron_manager:',
            'triumph' => '150 :triumph:',
            'endCause' => 'Assassiné',
            'daedalusId' => 42,
        ], $normalized);
    }

    public function testShouldNormalizeUserShipsHistoryViewModelWhenPlayerWasMush(): void
    {
        // given
        $viewModel = new UserShipsHistoryViewModel(
            characterName: 'jin_su',
            daysSurvived: 10,
            nbExplorations: 5,
            nbNeronProjects: 3,
            nbResearchProjects: 2,
            nbScannedPlanets: 7,
            titles: [],
            triumph: 150,
            endCause: 'assassinated',
            daedalusId: 42,
            playerWasMush: true,
        );

        // when
        $normalized = $this->normalizer->normalize($viewModel, null, ['language' => 'fr']);

        // then
        self::assertEquals(
            [
                'characterName' => ':jin_su: Jin Su',
                'daysSurvived' => 10,
                'nbExplorations' => 5,
                'nbNeronProjects' => 3,
                'nbResearchProjects' => 2,
                'nbScannedPlanets' => 7,
                'titles' => '',
                'triumph' => '150 :triumph_mush:',
                'endCause' => 'Assassiné',
                'daedalusId' => 42,
            ],
            $normalized
        );
    }

    private function stubTranslationService(array $translations): TranslationServiceInterface
    {
        return new class($translations) implements TranslationServiceInterface {
            private array $translations;

            public function __construct(array $translations)
            {
                $this->translations = $translations;
            }

            public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
            {
                return $this->translations[$key] ?? '';
            }
        };
    }
}
