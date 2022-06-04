<?php

namespace Mush\Test\Daedalus\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Daedalus\Service\DaedalusWidgetServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusNormalizerTest extends TestCase
{
    private DaedalusNormalizer $normalizer;

    /** @var CycleServiceInterface|Mockery\Mock */
    private CycleServiceInterface $cycleService;
    /** @var TranslationService|Mockery\Mock */
    private TranslationService $translationService;
    /** @var TranslatorInterface|Mockery\Mock */
    private DaedalusWidgetServiceInterface $daedalusWidgetService;

    /**
     * @before
     */
    public function before()
    {
        $this->cycleService = Mockery::mock(CycleServiceInterface::class);
        $this->translationService = Mockery::mock(TranslationService::class);
        $this->daedalusWidgetService = Mockery::mock(DaedalusWidgetServiceInterface::class);

        $this->normalizer = new DaedalusNormalizer($this->cycleService, $this->translationService, $this->daedalusWidgetService);
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
        $daedalus->setPlaces(new ArrayCollection());
        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus->setGameConfig($gameConfig);

        $daedalusConfig
            ->setMaxFuel(100)
            ->setMaxHull(100)
            ->setMaxOxygen(100)
        ;
        $daedalus
            ->setCycle(4)
            ->setDay(4)
            ->setHull(100)
            ->setOxygen(24)
            ->setFuel(24)
            ->setShield(100)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('oxygen.name', ['quantity' => 24, 'maximum' => 100], 'daedalus')
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('oxygen.description', [], 'daedalus')
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('fuel.name', ['quantity' => 24, 'maximum' => 100], 'daedalus')
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('fuel.description', [], 'daedalus')
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('hull.name', ['quantity' => 100, 'maximum' => 100], 'daedalus')
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('hull.description', [], 'daedalus')
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('shield.name', ['quantity' => 100], 'daedalus')
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('shield.description', [], 'daedalus')
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('currentCycle.name', [], 'daedalus')
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('currentCycle.description', [], 'daedalus')
            ->andReturn('translated two')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('crewPlayer.name', [], 'daedalus')
            ->andReturn('translated one')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('crewPlayer.description', [
                'cryogenizedPlayers' => 0,
                'playerAlive' => 0,
                'humanDead' => 0,
                'mushAlive' => 0,
                'mushDead' => 0, ],
                'daedalus')
            ->andReturn('translated two')
            ->once()
        ;

        $this->daedalusWidgetService->shouldReceive('getMinimap')->with($daedalus)->andReturn([])->once();
        $data = $this->normalizer->normalize($daedalus);

        $expected = [
            'id' => 2,
            'game_config' => null,
            'cycle' => 4,
            'day' => 4,
            'oxygen' => [
                'quantity' => 24,
                'name' => 'translated one',
                'description' => 'translated two', ],
            'fuel' => [
                'quantity' => 24,
                'name' => 'translated one',
                'description' => 'translated two', ],
            'hull' => [
                'quantity' => 100,
                'name' => 'translated one',
                'description' => 'translated two', ],
            'shield' => [
                'quantity' => 100,
                'name' => 'translated one',
                'description' => 'translated two', ],
            'nextCycle' => $nextCycle->format(\DateTime::ATOM),
            'cryogenizedPlayers' => 0,
            'humanPlayerAlive' => 0,
            'humanPlayerDead' => 0,
            'mushPlayerAlive' => 0,
            'mushPlayerDead' => 0,
            'currentCycle' => [
                'name' => 'translated one',
                'description' => 'translated two', ],
            'crewPlayer' => [
                'name' => 'translated one',
                'description' => 'translated two', ],
            'minimap' => [],
        ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
