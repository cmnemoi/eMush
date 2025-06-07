<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Normalizer;

use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Player\Normalizer\PlayerHighlightNormalizer;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerHighlightNormalizerCest extends AbstractFunctionalTest
{
    private PlayerHighlightNormalizer $normalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(PlayerHighlightNormalizer::class);
    }

    public function shouldNormalizeActionHighlight(FunctionalTester $I): void
    {
        $actionHighlight = new PlayerHighlight(
            name: ActionEnum::SCAN->toString(),
            actionResult: (new Success())->getName(),
        );

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);

        $I->assertEquals('Vous avez découvert une nouvelle planète !', $normalized);
    }
}
