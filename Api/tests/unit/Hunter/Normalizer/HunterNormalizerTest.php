<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Hunter\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Normalizer\HunterNormalizer;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use PHPUnit\Framework\TestCase;

final class HunterNormalizerTest extends TestCase
{
    private const ASTEROID_DESCRIPTION = 'Un gros caillou qui se rapproche dangereusement du Daedalus
    Pas de poursuite. 
    Dans 6 cycles, va collisionner le Daedalus infligeant 20 points de dégâts.';
    private const HUNTER_DESCRIPTION = 'Chasseur standard de la FDS';

    private HunterNormalizer $normalizer;
    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new HunterNormalizer($this->gearToolService, $this->translationService);
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
        $currentPlayer = $this->createMock(Player::class);
        $daedalus = $this->createMock(Daedalus::class);
        $hunter = $this->createMock(Hunter::class);

        $context = [
            'currentPlayer' => $currentPlayer,
            'hunter' => $hunter,
        ];

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
                HunterEnum::ASTEROID . '.name',
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
                HunterEnum::ASTEROID . '.description',
                [
                    'charges' => 6,
                    'health' => 20,
                ],
                'hunter',
                LanguageEnum::FRENCH
            )
            ->andReturn(self::ASTEROID_DESCRIPTION)
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($currentPlayer, [ActionScopeEnum::ROOM], Hunter::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => HunterEnum::ASTEROID,
            'name' => 'Astéroïde',
            'description' => self::ASTEROID_DESCRIPTION,
            'health' => 20,
            'charges' => 6,
            'actions' => [],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($hunter, context: $context));
    }

    public function testNormalizeReturnsNullChargesForNonAsteroidHunter(): void
    {
        $action = $this->createMock(Action::class);
        $currentPlayer = $this->createMock(Player::class);
        $daedalus = $this->createMock(Daedalus::class);
        $hunter = $this->createMock(Hunter::class);

        $context = [
            'currentPlayer' => $currentPlayer,
            'hunter' => $hunter,
        ];

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $hunter->method('getId')->willReturn(1);
        $hunter->method('getName')->willReturn(HunterEnum::HUNTER);
        $hunter->method('getHealth')->willReturn(6);
        $hunter->method('getStatusByName')->willReturn(null);
        $hunter->method('getDaedalus')->willReturn($daedalus);

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                HunterEnum::HUNTER . '.name',
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
                HunterEnum::HUNTER . '.description',
                [
                    'charges' => null,
                    'health' => 6,
                ],
                'hunter',
                LanguageEnum::FRENCH
            )
            ->andReturn(self::HUNTER_DESCRIPTION)
            ->once()
        ;

        $this->gearToolService
            ->shouldReceive('getActionsTools')
            ->with($currentPlayer, [ActionScopeEnum::ROOM], Hunter::class)
            ->andReturn(new ArrayCollection([]))
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => HunterEnum::HUNTER,
            'name' => 'Hunter',
            'description' => self::HUNTER_DESCRIPTION,
            'health' => 6,
            'charges' => null,
            'actions' => [],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($hunter, context: $context));
    }
}
