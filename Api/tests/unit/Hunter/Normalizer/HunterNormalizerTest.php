<?php

declare(strict_types=1);

namespace Tests\Mush\Hunter\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Normalizer\HunterNormalizer;
use Mush\Status\Entity\ChargeStatus;
use PHPUnit\Framework\TestCase;

final class HunterNormalizerTest extends TestCase
{   

    private const ASTEROID_DESCRIPTION = 'Un gros caillou qui se rapproche dangereusement du Daedalus
    Pas de poursuite. 
    Dans 6 cycles, va collisionner le Daedalus infligeant 20 points de dégâts.';
    private const HUNTER_DESCRIPTION = 'Chasseur standard de la FDS';

    private HunterNormalizer $normalizer;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {   
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new HunterNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testSupportsNormalizationReturnsTrueForHunterNotInPool(): void
    {
        $hunter = $this->createMock(Hunter::class);
        $hunter->method('isInPool')->willReturn(false);

        $this->assertTrue($this->normalizer->supportsNormalization($hunter));
    }

    public function testSupportsNormalizationReturnsFalseForHunterInPool(): void
    {
        $hunter = $this->createMock(Hunter::class);
        $hunter->method('isInPool')->willReturn(true);

        $this->assertFalse($this->normalizer->supportsNormalization($hunter));
    }

    public function testNormalizeReturnsExpectedArray(): void
    {   
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $daedalus = $this->createMock(Daedalus::class);
        $hunter = $this->createMock(Hunter::class);
        
        $chargeStatus->method('getCharge')->willReturn(6);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $hunter->method('getId')->willReturn(1);
        $hunter->method('getName')->willReturn(HunterEnum::ASTEROID);
        $hunter->method('getHealth')->willReturn(20);
        $hunter->method('getStatusByName')->willReturn($chargeStatus);
        $hunter->method('getDaedalus')->willReturn($daedalus);

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                HunterEnum::ASTEROID,
                [],
                'hunter',
                LanguageEnum::FRENCH
            )
            ->andReturn('Astéroïde')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                HunterEnum::ASTEROID . '_description',
                [],
                'hunter',
                LanguageEnum::FRENCH
            )
            ->andReturn(self::ASTEROID_DESCRIPTION)
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => HunterEnum::ASTEROID,
            'name' => 'Astéroïde',
            'description' => self::ASTEROID_DESCRIPTION,
            'health' => 20,
            'charges' => 6,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($hunter));
    }

    public function testNormalizeReturnsNullChargesForNonAsteroidHunter(): void
    {   
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $daedalus = $this->createMock(Daedalus::class);
        $hunter = $this->createMock(Hunter::class);

        $chargeStatus->method('getCharge')->willReturn(1);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $hunter->method('getId')->willReturn(1);
        $hunter->method('getName')->willReturn(HunterEnum::HUNTER);
        $hunter->method('getHealth')->willReturn(6);
        $hunter->method('getStatusByName')->willReturn($chargeStatus);
        $hunter->method('getDaedalus')->willReturn($daedalus);

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                HunterEnum::HUNTER,
                [],
                'hunter',
                LanguageEnum::FRENCH
            )
            ->andReturn('Hunter')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                HunterEnum::HUNTER . '_description',
                [],
                'hunter',
                LanguageEnum::FRENCH
            )
            ->andReturn(self::HUNTER_DESCRIPTION)
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => HunterEnum::HUNTER,
            'name' => 'Hunter',
            'description' => self::HUNTER_DESCRIPTION,
            'health' => 6,
            'charges' => null,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($hunter));
    }
}
