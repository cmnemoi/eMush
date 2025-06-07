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

    public function testShouldNormalizeSimpleHighlight(): void
    {
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SCAN,
            actionResult: new Success(),
        );

        $this->translationService
            ->shouldReceive('translate')
            ->with('scan.highlight', ['result' => 'success'], 'actions', LanguageEnum::FRENCH)
            ->andReturn('Vous avez découvert une nouvelle planète !')
            ->once();

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);

        self::assertEquals('Vous avez découvert une nouvelle planète !', $normalized);
    }

    public function testShouldNormalizeHighlightWithPlayerTarget(): void
    {
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::HIT,
            target: PlayerFactory::createPlayerByName(CharacterEnum::CHUN),
            actionResult: new Success(),
        );

        $this->translationService
            ->shouldReceive('translate')
            ->with('hit.highlight', ['character' => 'chun', 'result' => 'success'], 'actions', LanguageEnum::FRENCH)
            ->andReturn('Vous avez frappé **Chun**.')
            ->once();

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);
        self::assertEquals('Vous avez frappé **Chun**.', $normalized);
    }

    public function testShouldNormalizeHighlightWithEquipmentParameter(): void
    {
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SABOTAGE,
            target: GameEquipmentFactory::createEquipmentByName(EquipmentEnum::BIOS_TERMINAL),
            actionResult: new Success(),
        );

        $this->translationService
            ->shouldReceive('translate')
            ->with('sabotage.highlight', ['equipment' => 'bios_terminal', 'result' => 'success'], 'actions', LanguageEnum::FRENCH)
            ->andReturn('Vous avez saboté un **Terminal BIOS**.')
            ->once();

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);
        self::assertEquals('Vous avez saboté un **Terminal BIOS**.', $normalized);
    }

    public function testShouldNormalizeHighlightWithPlaceParameter(): void
    {
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::SPREAD_FIRE,
            target: Place::createRoomByName(RoomEnum::LABORATORY),
            actionResult: new Success(),
        );

        $this->translationService
            ->shouldReceive('translate')
            ->with('spread_fire.highlight', ['place' => 'laboratory', 'result' => 'success'], 'actions', LanguageEnum::FRENCH)
            ->andReturn('Vous avez mis le feu au **Laboratoire**.')
            ->once();

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);
        self::assertEquals('Vous avez mis le feu au **Laboratoire**.', $normalized);
    }

    public function testShouldNormalizeHighlightFromFailedAction(): void
    {
        $actionHighlight = new ActionHighlight(
            actionName: ActionEnum::ATTACK,
            target: PlayerFactory::createPlayerByName(CharacterEnum::CHUN),
            actionResult: new Fail(),
        );

        $this->translationService
            ->shouldReceive('translate')
            ->with('attack.highlight', ['character' => 'chun', 'result' => 'fail'], 'actions', LanguageEnum::FRENCH)
            ->andReturn('Vous avez tenté d\'agresser **Chun** sans succès...')
            ->once();

        $normalized = $this->normalizer->normalize($actionHighlight, format: null, context: ['language' => LanguageEnum::FRENCH]);
        self::assertEquals('Vous avez tenté d\'agresser **Chun** sans succès...', $normalized);
    }
}
