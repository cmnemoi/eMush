<?php

namespace Mush\Tests\unit\Daedalus\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class DaedalusNormalizerTest extends TestCase
{
    private DaedalusNormalizer $normalizer;

    /** @var CycleServiceInterface|Mockery\Mock */
    private CycleServiceInterface $cycleService;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    /** @var Mockery\Mock|PlanetServiceInterface */
    private PlanetServiceInterface $planetService;

    /** @var Mockery\Mock|NormalizerInterface */
    private NormalizerInterface $projectNormalizer;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->cycleService = \Mockery::mock(CycleServiceInterface::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->planetService = \Mockery::mock(PlanetServiceInterface::class);

        $this->projectNormalizer = \Mockery::mock(NormalizerInterface::class);

        $this->normalizer = new DaedalusNormalizer($this->cycleService, $this->translationService, $this->planetService);
        $this->normalizer->setNormalizer($this->projectNormalizer);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testNormalizer(): void
    {
        $nextCycle = new \DateTime();
        $this->cycleService->shouldReceive('getDateStartNextCycle')->andReturn($nextCycle);
        $daedalus = \Mockery::mock(Daedalus::class);
        $daedalus->shouldReceive('getId')->andReturn(2);
        $daedalus->shouldReceive('hasStatus')->andReturn(false)->once();
        $daedalus->shouldReceive('getAttackingHunters')->andReturn(new HunterCollection());
        $daedalus->shouldReceive('getLanguage')->andReturn(LanguageEnum::FRENCH);
        $daedalus->shouldReceive('getPilgred')->andReturn(ProjectFactory::createPilgredProject());
        $daedalus->shouldReceive('hasFinishedProject')->with(ProjectName::PLASMA_SHIELD)->andReturn(false);
        $daedalus->shouldReceive('getFinishedNeronProjects')->andReturn(new ArrayCollection());
        $daedalus->shouldReceive('getFinishedResearchProjects')->andReturn(new ArrayCollection());
        $daedalus->makePartial();
        $daedalus->setPlayers(new ArrayCollection());
        $daedalus->setPlaces(new ArrayCollection());

        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalusConfig = new DaedalusConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalusConfig
            ->setMaxFuel(100)
            ->setMaxHull(100)
            ->setMaxOxygen(100)
            ->setMaxShield(100)
            ->setInitFuel(24)
            ->setInitHull(100)
            ->setInitOxygen(24)
            ->setInitShield(100)
            ->setPlayerCount(0);

        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $daedalus
            ->setCycle(4)
            ->setDay(4)
            ->setDaedalusVariables($daedalusConfig);

        $explorator = self::createStub(Player::class);
        $explorator->method('getLogName')->willReturn('roland');
        $explorator->method('hasStatus')->willReturn(false);
        $explorator->method('getDaedalus')->willReturn($daedalus);

        $exploration = self::createStub(Exploration::class);
        $exploration->method('getAliveExplorators')->willReturn(new PlayerCollection([$explorator]));
        $exploration->method('getCycle')->willReturn(1);
        $exploration->method('getCycleLength')->willReturn(10);
        $exploration->method('getEstimatedDuration')->willReturn(10);

        $daedalus->setExploration($exploration);

        $this->translationService
            ->shouldReceive('translate')
            ->with('oxygen.name', ['quantity' => 24, 'maximum' => 100], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('oxygen.description', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('fuel.name', ['quantity' => 24, 'maximum' => 100], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('fuel.description', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('hull.name', ['quantity' => 100, 'maximum' => 100], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('hull.description', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('currentCycle.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('currentCycle.description', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated current cycle description')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('crewPlayer.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('calendar.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated calendar name')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('calendar.description', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated calendar description')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('cycle.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated cycle name')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('day.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated day name')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                'crewPlayer.description',
                [
                    'cryogenizedPlayers' => 0,
                    'playerAlive' => 0,
                    'playerDead' => 0,
                    'mushAlive' => 0,
                    'mushDead' => 0,
                ],
                'daedalus',
                LanguageEnum::FRENCH
            )
            ->andReturn('translated two')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('planet_name', [], 'planet', 'fr')
            ->andReturn('translated planet name')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('exploration_pop_up.planet', ['planetName' => 'translated planet name'], 'misc', 'fr')
            ->andReturn('Planète : translated planet name')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('exploration_pop_up.explorators', ['explorators' => 'Roland'], 'misc', 'fr')
            ->andReturn('Équipe : Roland')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('exploration_pop_up.estimated_duration', ['estimatedDuration' => 10], 'misc', 'fr')
            ->andReturn('Retour estimé : 10 minutes')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('exploration_pop_up.title', [], 'misc', 'fr')
            ->andReturn('Expédition en cours')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('roland.name', [], 'characters', 'fr')
            ->andReturn('Roland')
            ->once();

        $this->planetService
            ->shouldReceive('findPlanetInDaedalusOrbit')
            ->with($daedalus)
            ->andReturn(null)
            ->once();

        $data = $this->normalizer->normalize($daedalus);

        $expected = [
            'id' => 2,
            'game_config' => null,
            'oxygen' => [
                'quantity' => 24,
                'name' => 'translated one',
                'description' => 'translated two',
            ],
            'fuel' => [
                'quantity' => 24,
                'name' => 'translated one',
                'description' => 'translated two',
            ],
            'hull' => [
                'quantity' => 100,
                'name' => 'translated one',
                'description' => 'translated two',
            ],
            'shield' => null,
            'timer' => [
                'name' => 'translated one',
                'description' => 'translated current cycle description',
                'timerCycle' => $nextCycle->format(\DateTimeInterface::ATOM),
            ],
            'calendar' => [
                'name' => 'translated calendar name',
                'description' => 'translated calendar description',
                'cycle' => 4,
                'cycleName' => 'translated cycle name',
                'day' => 4,
                'dayName' => 'translated day name',
            ],
            'cryogenizedPlayers' => 0,
            'humanPlayerAlive' => 0,
            'humanPlayerDead' => 0,
            'mushPlayerAlive' => 0,
            'mushPlayerDead' => 0,
            'crewPlayer' => [
                'name' => 'translated one',
                'description' => 'translated two',
            ],
            'inOrbitPlanet' => null,
            'isDaedalusTravelling' => false,
            'attackingHunters' => 0,
            'onGoingExploration' => [
                'title' => 'Expédition en cours',
                'planet' => 'Planète : translated planet name',
                'explorators' => 'Équipe : Roland',
                'estimatedDuration' => 'Retour estimé : 10 minutes',
            ],
            'projects' => [
                'neronProjects' => [],
                'researchProjects' => [],
            ],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testShouldNormalizeFinishedPilgred(): void
    {
        // given I have a finished PILGRED project
        $pilgred = ProjectFactory::createPilgredProject();
        $pilgred->makeProgressAndUpdateParticipationDate(100);
        $daedalus = $pilgred->getDaedalus();
        ProjectFactory::createPlasmaShieldProjectForDaedalus($daedalus);

        $this->planetService->shouldIgnoreMissing();
        $this->translationService->shouldIgnoreMissing();
        $this->cycleService->shouldReceive('getDateStartNextCycle')->andReturn(new \DateTime());

        $this->projectNormalizer->shouldReceive('normalize')->andReturn([
            'type' => 'PILGRED',
            'key' => 'pilgred',
            'name' => 'PILGRED',
            'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
        ]);

        // when I normalize the daedalus
        $data = $this->normalizer->normalize($daedalus);

        // then I should have see Pilgred in the data
        self::assertEquals(
            expected: [
                'type' => 'PILGRED',
                'key' => 'pilgred',
                'name' => 'PILGRED',
                'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
            ],
            actual: $data['projects']['pilgred'],
        );
    }

    public function testShouldNormalizeFinishedNeronProjects(): void
    {
        // given I have 2 finished NERON projects
        $daedalus = DaedalusFactory::createDaedalus();
        $projects = [
            ProjectFactory::createDummyNeronProjectForDaedalus($daedalus),
            ProjectFactory::createDummyNeronProjectForDaedalus($daedalus),
        ];
        array_map(static fn ($project) => $project->makeProgressAndUpdateParticipationDate(100), $projects);

        // setup
        ProjectFactory::createPilgredProjectForDaedalus($daedalus);
        ProjectFactory::createPlasmaShieldProjectForDaedalus($daedalus);
        $this->planetService->shouldIgnoreMissing();
        $this->translationService->shouldIgnoreMissing();
        $this->cycleService->shouldReceive('getDateStartNextCycle')->andReturn(new \DateTime());
        $this->projectNormalizer->shouldReceive('normalize')->andReturn([
            'type' => 'Proj.',
            'key' => '',
            'name' => '',
            'description' => '',
        ]);

        // when I normalize the daedalus
        $data = $this->normalizer->normalize($daedalus);

        // then I should have 2 normalized Neron projects in the data
        self::assertCount(2, $data['projects']['neronProjects']);
        self::assertEquals(
            expected: [
                'type' => 'Proj.',
                'key' => '',
                'name' => '',
                'description' => '',
            ],
            actual: $data['projects']['neronProjects'][0],
        );
    }

    public function testShouldNormalizeFinishedResearchProjects(): void
    {
        // given I have 2 finished research projects
        $daedalus = DaedalusFactory::createDaedalus();
        $projects = [
            ProjectFactory::createDummyResearchForDaedalus($daedalus),
            ProjectFactory::createDummyResearchForDaedalus($daedalus),
        ];
        array_map(static fn ($project) => $project->makeProgressAndUpdateParticipationDate(100), $projects);

        // setup
        ProjectFactory::createPilgredProjectForDaedalus($daedalus);
        ProjectFactory::createPlasmaShieldProjectForDaedalus($daedalus);
        $this->planetService->shouldIgnoreMissing();
        $this->translationService->shouldIgnoreMissing();
        $this->cycleService->shouldReceive('getDateStartNextCycle')->andReturn(new \DateTime());
        $this->projectNormalizer->shouldReceive('normalize')->andReturn([
            'type' => 'Res.',
            'key' => '',
            'name' => '',
            'description' => '',
        ]);

        // when I normalize the daedalus
        $data = $this->normalizer->normalize($daedalus);

        // then I should have 2 normalized research projects in the data
        self::assertCount(2, $data['projects']['researchProjects']);
        self::assertEquals(
            expected: [
                'type' => 'Res.',
                'key' => '',
                'name' => '',
                'description' => '',
            ],
            actual: $data['projects']['researchProjects'][0],
        );
    }
}
