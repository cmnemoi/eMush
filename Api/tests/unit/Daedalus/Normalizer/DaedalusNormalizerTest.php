<?php

namespace Mush\Test\Daedalus\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusNormalizerTest extends TestCase
{
    private DaedalusNormalizer $normalizer;
    /** @var CycleServiceInterface | Mockery\Mock */
    private CycleServiceInterface $cycleService;

    /** @var TranslatorInterface | Mockery\Mock */
    private TranslatorInterface $translator;

    /**
     * @before
     */
    public function before()
    {
        $this->cycleService = Mockery::mock(CycleServiceInterface::class);
        $this->translator = Mockery::mock(TranslatorInterface::class);

        $this->normalizer = new DaedalusNormalizer($this->cycleService, $this->translator);
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
        $nextCycle = new \DateTime();
        $this->cycleService->shouldReceive('getDateStartNextCycle')->andReturn($nextCycle);
        $daedalus = Mockery::mock(Daedalus::class);
        $daedalus->shouldReceive('getId')->andReturn(2);
        $daedalus->makePartial();
        $daedalus->setPlayers(new ArrayCollection());
        $daedalus->setRooms(new ArrayCollection());
        $daedalus->setGameConfig(new GameConfig());

        $daedalus
            ->setCycle(4)
            ->setDay(4)
            ->setHull(100)
            ->setOxygen(24)
            ->setFuel(24)
            ->setShield(100)
        ;

        $data = $this->normalizer->normalize($daedalus);

        $expected = [
            'id' => 2,
            'cycle' => 4,
            'day' => 4,
            'oxygen' => 24,
            'fuel' => 24,
            'hull' => 100,
            'shield' => 100,
            'nextCycle' => $nextCycle->format(\DateTime::ATOM),
            'cryogenizedPlayers' => 0,
            'humanPlayerAlive' => 0,
            'humanPlayerDead' => 0,
            'mushPlayerAlive' => 0,
            'mushPlayerDead' => 0,
            'minimap' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
