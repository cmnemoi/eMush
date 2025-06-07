<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Normalizer;

use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Normalizer\ActionHighlightNormalizer;
use Mush\Action\ValueObject\ActionHighlight;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionHighlightNormalizerCest extends AbstractFunctionalTest
{
    private ActionHighlightNormalizer $normalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(ActionHighlightNormalizer::class);
    }

    public function shouldNormalizeActionHighlight(FunctionalTester $I): void
    {
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SCAN,
            actionResult: new Success(),
        );

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);

        $I->assertEquals('Vous avez découvert une nouvelle planète !', $normalized);
    }
}
