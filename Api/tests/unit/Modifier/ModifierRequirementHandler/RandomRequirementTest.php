<?php

namespace Mush\Tests\unit\Modifier\ModifierRequirementHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\RequirementRandom;
use Mush\Place\Entity\Place;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RandomRequirementTest extends TestCase
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private RequirementRandom $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new RequirementRandom(
            $this->randomService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testRandomActivationRequirementModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $modifierActivationRequirement->setValue(50);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(true);
        $result = $this->service->checkRequirement($modifierActivationRequirement, $room);
        self::assertTrue($result);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(false);
        $result = $this->service->checkRequirement($modifierActivationRequirement, $room);
        self::assertFalse($result);
    }
}
