<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Hunter\Normalizer;

use Mockery;
use Mush\Action\Entity\ActionConfig;
use Mush\Communications\Factory\TradeFactory;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Normalizer\HunterNormalizer;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryTradeRepository;
use Mush\Tests\unit\Hunter\TestDoubles\InMemoryHunterRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HunterNormalizerTest extends TestCase
{
    private const ASTEROID_DESCRIPTION = 'Un gros caillou qui se rapproche dangereusement du Daedalus
    Pas de poursuite.
    Dans 6 cycles, va collisionner le Daedalus infligeant 20 points de dégâts.';
    private const HUNTER_DESCRIPTION = 'Chasseur standard de la FDS';

    private HunterNormalizer $normalizer;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    private TradeRepositoryInterface $tradeRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->tradeRepository = new InMemoryTradeRepository();
        $this->normalizer = new HunterNormalizer($this->tradeRepository, $this->translationService);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testSupportsNormalizationReturnsTrueForHunterNotInPool(): void
    {
        $hunter = $this->createMock(Hunter::class);
        $hunter->method('isInPool')->willReturn(false);

        self::assertTrue($this->normalizer->supportsNormalization($hunter));
    }

    public function testSupportsNormalizationReturnsFalseForHunterInPool(): void
    {
        $hunter = $this->createMock(Hunter::class);
        $hunter->method('isInPool')->willReturn(true);

        self::assertFalse($this->normalizer->supportsNormalization($hunter));
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
            ->once();

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
            ->once();

        $expected = [
            'id' => 1,
            'key' => HunterEnum::ASTEROID,
            'name' => 'Astéroïde',
            'description' => self::ASTEROID_DESCRIPTION,
            'health' => 20,
            'charges' => 6,
            'actions' => [],
        ];

        self::assertSame($expected, $this->normalizer->normalize($hunter, context: $context));
    }

    public function testNormalizeReturnsNullChargesForNonAsteroidHunter(): void
    {
        $action = $this->createMock(ActionConfig::class);
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
            ->once();

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
            ->once();

        $expected = [
            'id' => 1,
            'key' => HunterEnum::HUNTER,
            'name' => 'Hunter',
            'description' => self::HUNTER_DESCRIPTION,
            'health' => 6,
            'charges' => null,
            'actions' => [],
        ];

        self::assertSame($expected, $this->normalizer->normalize($hunter, context: $context));
    }

    public function testNormalizeReturnsTransportImage(): void
    {
        $hunter = new Hunter(
            hunterConfig: HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::TRANSPORT)),
            daedalus: DaedalusFactory::createDaedalus(),
        );
        (new InMemoryHunterRepository())->save($hunter);

        $trade = TradeFactory::createForestDealTrade(requiredHydropot: 1, offeredOxygen: 1, transportId: $hunter->getId());
        $this->tradeRepository->save($trade);

        $this->translationService->shouldIgnoreMissing();

        $normalizedHunter = $this->normalizer->normalize($hunter, context: ['currentPlayer' => Player::createNull()]);

        self::assertEquals('transport_2', $normalizedHunter['transportImage']);
    }
}
