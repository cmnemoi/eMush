<?php

namespace Mush\Test\Daedalus\Normalizer;

use Mockery;
use DateTime;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigService;
use Mush\Game\Service\CycleServiceInterface;
use PHPUnit\Framework\TestCase;

class DaedalusNormalizerTest extends TestCase
{
    private DaedalusNormalizer $normalizer;
    /** @var GameConfigService | Mockery\Mock */
    private GameConfig $gameConfig;
    /** @var CycleServiceInterface | Mockery\Mock */
    private CycleService $cycleService;


    /**
     * @before
     */
    public function before()
    {
        $gameConfigService = Mockery::mock(GameConfigService::class);
        $cycleService = Mockery::mock(CycleServiceInterface::class);

        $this->gameConfig = new GameConfig();

        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();
        $this->normalizer = new DaedalusNormalizer($cycleService, $gameConfigService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNormalizer()
    {
        $daedalus = Mockery::mock(Daedalus::class);
        $daedalus->shouldReceive('getId')->andReturn(2);
        $daedalus->makePartial();

        $daedalus
            ->setCycle(4)
            ->setDay(4)
            ->setHull(100)
            ->setOxygen(24)
            ->setFuel(24)
            ->setShield(100)
        ;

        $this->cycleService->souldReceive('getDateStartNextCycle')->andReturn(date('2020-11-27 12:0:0'))->once();

        $data = $this->normalizer->normalize($daedalus);

        $expected = [
            'id' => 2,
            'cycle' => 4,
            'day' => 4,
            'oxygen' => 24,
            'fuel' => 24,
            'hull' => 100,
            'shield' => 100,
            'createdAt' => $daedalus->getCreatedAt(),
            'updatedAt' => $daedalus->getUpdatedAt(),
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
