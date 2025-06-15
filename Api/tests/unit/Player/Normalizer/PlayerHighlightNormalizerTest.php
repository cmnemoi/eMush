<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\Normalizer;

use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\PlayerHighlightNormalizer;
use Mush\Player\ValueObject\PlayerHighlight;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerHighlightNormalizerTest extends TestCase
{
    private PlayerHighlightNormalizer $normalizer;
    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new PlayerHighlightNormalizer($this->translationService);
    }

    public function testShouldNormalizeSimplePlayerHighlight(): void
    {
        // Given
        $playerHighlight = new PlayerHighlight(
            name: ActionEnum::SCAN->toString(),
            author: [],
            result: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn('Vous avez découvert une nouvelle planète !');

        // When
        $normalized = $this->whenNormalizingPlayerHighlight($playerHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez découvert une nouvelle planète !', $normalized);
    }

    public function testShouldNormalizePlayerHighlightWithPlayerTarget(): void
    {
        // Given
        $chun = PlayerFactory::createPlayerByName(CharacterEnum::CHUN);
        $playerHighlight = new PlayerHighlight(
            name: ActionEnum::HIT->toString(),
            author: [],
            target: ['target_' . $chun->getLogKey() => $chun->getLogName()],
            result: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez frappé **Chun**.',
            'hit.highlight',
            ['target_character' => 'chun']
        );

        // When
        $normalized = $this->whenNormalizingPlayerHighlight($playerHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez frappé **Chun**.', $normalized);
    }

    public function testShouldNormalizePlayerHighlightWithEquipmentTarget(): void
    {
        // Given
        $biosTerminal = GameEquipmentFactory::createEquipmentByName(EquipmentEnum::BIOS_TERMINAL);
        $playerHighlight = new PlayerHighlight(
            name: ActionEnum::SABOTAGE->toString(),
            author: [],
            target: ['target_' . $biosTerminal->getLogKey() => $biosTerminal->getLogName()],
            result: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez saboté un **Terminal BIOS**.',
            'sabotage.highlight',
            ['target_equipment' => 'bios_terminal']
        );

        // When
        $normalized = $this->whenNormalizingPlayerHighlight($playerHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez saboté un **Terminal BIOS**.', $normalized);
    }

    public function testShouldNormalizePlayerHighlightWithPlaceTarget(): void
    {
        // Given
        $laboratory = Place::createRoomByName(RoomEnum::LABORATORY);
        $playerHighlight = new PlayerHighlight(
            name: ActionEnum::SPREAD_FIRE->toString(),
            author: [],
            target: ['target_' . $laboratory->getLogKey() => $laboratory->getLogName()],
            result: (new Success())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez mis le feu au **Laboratoire**.',
            'spread_fire.highlight',
            ['target_place' => 'laboratory']
        );

        // When
        $normalized = $this->whenNormalizingPlayerHighlight($playerHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez mis le feu au **Laboratoire**.', $normalized);
    }

    public function testShouldNormalizeFailedPlayerHighlight(): void
    {
        // Given
        $chun = PlayerFactory::createPlayerByName(CharacterEnum::CHUN);
        $playerHighlight = new PlayerHighlight(
            name: ActionEnum::ATTACK->toString(),
            author: [],
            target: ['target_' . $chun->getLogKey() => $chun->getLogName()],
            result: (new Fail())->getName(),
        );
        $this->givenTranslationServiceWillReturn(
            'Vous avez tenté d\'agresser **Chun** sans succès...',
            'attack.highlight_fail',
            ['target_character' => 'chun']
        );

        // When
        $normalized = $this->whenNormalizingPlayerHighlight($playerHighlight);

        // Then
        $this->thenShouldReturnTranslatedHighlight('Vous avez tenté d\'agresser **Chun** sans succès...', $normalized);
    }

    private function givenTranslationServiceWillReturn(string $translation, string $key = 'scan.highlight', array $parameters = []): void
    {
        $this->translationService
            ->shouldReceive('translate')
            ->with($key, $parameters, 'highlight', LanguageEnum::FRENCH)
            ->andReturn($translation)
            ->once();
    }

    private function whenNormalizingPlayerHighlight(PlayerHighlight $playerHighlight): string
    {
        return $this->normalizer->normalize($playerHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);
    }

    private function thenShouldReturnTranslatedHighlight(string $expected, string $actual): void
    {
        self::assertEquals($expected, $actual);
    }
}
