<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Normalizer;

use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Normalizer\TriumphGainNormalizer;
use Mush\Triumph\ValueObject\TriumphGain;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TriumphGainNormalizerTest extends TestCase
{
    private TriumphGainNormalizer $normalizer;

    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = $this->createStub(TranslationServiceInterface::class);
        $this->normalizer = new TriumphGainNormalizer($this->translationService);
    }

    public function testPositiveGain(): void
    {
        $gain = new TriumphGain(TriumphEnum::CYCLE_HUMAN, 1, 1);

        $this->translationService->method('translate')->willReturn('Cycle Humain');

        $data = $this->normalizer->normalize($gain, null, ['language' => LanguageEnum::FRENCH]);

        self::assertEquals('1 x Cycle Humain ( +1 :triumph: )', $data);
    }

    public function testNegativeGain(): void
    {
        $gain = new TriumphGain(TriumphEnum::CYCLE_HUMAN, -1, 1);

        $this->translationService->method('translate')->willReturn('Cycle Humain');

        $data = $this->normalizer->normalize($gain, null, ['language' => LanguageEnum::FRENCH]);

        self::assertEquals('1 x Cycle Humain ( -1 :triumph: )', $data);
    }

    public function testMushGain(): void
    {
        $gain = new TriumphGain(TriumphEnum::CYCLE_MUSH, 1, 1, true);

        $this->translationService->method('translate')->willReturn('Cycle Mush');

        $data = $this->normalizer->normalize($gain, null, ['language' => LanguageEnum::FRENCH]);

        self::assertEquals('1 x Cycle Mush ( +1 :triumph_mush: )', $data);
    }
}
