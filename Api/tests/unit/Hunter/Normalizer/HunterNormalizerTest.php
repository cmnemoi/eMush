<?php

declare(strict_types=1);

namespace Tests\Mush\Hunter\Normalizer;

use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Normalizer\HunterNormalizer;
use Mush\Status\Entity\ChargeStatus;
use PHPUnit\Framework\TestCase;

final class HunterNormalizerTest extends TestCase
{
    private HunterNormalizer $normalizer;

    /**
     * @before
     */
    public function before()
    {
        $this->normalizer = new HunterNormalizer();
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
        $hunter = $this->createMock(Hunter::class);
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $chargeStatus->method('getCharge')->willReturn(6);

        $hunter->method('getId')->willReturn(1);
        $hunter->method('getName')->willReturn(HunterEnum::ASTEROID);
        $hunter->method('getHealth')->willReturn(20);
        $hunter->method('getStatusByName')->willReturn($chargeStatus);

        $expected = [
            'id' => 1,
            'name' => HunterEnum::ASTEROID,
            'health' => 20,
            'charges' => 6,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($hunter));
    }

    public function testNormalizeReturnsNullChargesForNonAsteroidHunter(): void
    {
        $hunter = $this->createMock(Hunter::class);
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $chargeStatus->method('getCharge')->willReturn(1);

        $hunter->method('getId')->willReturn(1);
        $hunter->method('getName')->willReturn(HunterEnum::HUNTER);
        $hunter->method('getHealth')->willReturn(6);
        $hunter->method('getStatusByName')->willReturn($chargeStatus);

        $expected = [
            'id' => 1,
            'name' => HunterEnum::HUNTER,
            'health' => 6,
            'charges' => null,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($hunter));
    }
}
