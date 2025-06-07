<?php

declare(strict_types=1);

namespace Mush\tests\unit\Action\Normalizer;

use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Normalizer\ActionHighlightNormalizer;
use Mush\Action\ValueObject\ActionHighlight;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ActionHighlightNormalizerTest extends TestCase
{
    private ActionHighlightNormalizer $normalizer;
    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new ActionHighlightNormalizer($this->translationService);
    }

    public function testShouldNormalizeSimpleActionHighlight(): void
    {
        // Given
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SCAN,
            actionResult: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn('Vous avez découvert une nouvelle planète !');

        // When
        $normalized = $this->whenNormalizingActionHighlight($actionHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez découvert une nouvelle planète !', $normalized);
    }

    public function testShouldNormalizeActionHighlightWithPlayerTarget(): void
    {
        // Given
        $chun = PlayerFactory::createPlayerByName(CharacterEnum::CHUN);
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::HIT,
            target: [$chun->getLogKey() => $chun->getLogName()],
            actionResult: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez frappé **Chun**.',
            'hit.highlight',
            ['character' => 'chun']
        );

        // When
        $normalized = $this->whenNormalizingActionHighlight($actionHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez frappé **Chun**.', $normalized);
    }

    public function testShouldNormalizeActionHighlightWithEquipmentTarget(): void
    {
        // Given
        $biosTerminal = GameEquipmentFactory::createEquipmentByName(EquipmentEnum::BIOS_TERMINAL);
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SABOTAGE,
            target: [$biosTerminal->getLogKey() => $biosTerminal->getLogName()],
            actionResult: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez saboté un **Terminal BIOS**.',
            'sabotage.highlight',
            ['equipment' => 'bios_terminal']
        );

        // When
        $normalized = $this->whenNormalizingActionHighlight($actionHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez saboté un **Terminal BIOS**.', $normalized);
    }

    public function testShouldNormalizeActionHighlightWithPlaceTarget(): void
    {
        // Given
        $laboratory = Place::createRoomByName(RoomEnum::LABORATORY);
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SPREAD_FIRE,
            target: [$laboratory->getLogKey() => $laboratory->getLogName()],
            actionResult: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez mis le feu au **Laboratoire**.',
            'spread_fire.highlight',
            ['place' => 'laboratory']
        );

        // When
        $normalized = $this->whenNormalizingActionHighlight($actionHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez mis le feu au **Laboratoire**.', $normalized);
    }

    public function testShouldNormalizeFailedActionHighlight(): void
    {
        // Given
        $chun = PlayerFactory::createPlayerByName(CharacterEnum::CHUN);
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::ATTACK,
            target: [$chun->getLogKey() => $chun->getLogName()],
            actionResult: (new Fail())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez tenté d\'agresser **Chun** sans succès...',
            'attack.highlight_fail',
            ['character' => 'chun']
        );

        // When
        $normalized = $this->whenNormalizingActionHighlight($actionHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez tenté d\'agresser **Chun** sans succès...', $normalized);
    }

    private function givenTranslationServiceWillReturn(string $translation, string $key = 'scan.highlight', array $parameters = []): void
    {
        $this->translationService
            ->shouldReceive('translate')
            ->with($key, $parameters, 'actions', LanguageEnum::FRENCH)
            ->andReturn($translation)
            ->once();
    }

    private function whenNormalizingActionHighlight(ActionHighlight $actionHighlight): string
    {
        return $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);
    }

    private function thenShouldReturnTranslatedHighlight(string $expected, string $actual): void
    {
        self::assertEquals($expected, $actual);
    }
}
