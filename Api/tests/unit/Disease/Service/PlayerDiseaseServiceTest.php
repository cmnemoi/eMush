<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var DiseaseConfigRepository | Mockery\Mock */
    private DiseaseConfigRepository $diseaseConfigRepository;

    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->diseaseConfigRepository = Mockery::mock(DiseaseConfigRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->playerDiseaseService = new PlayerDiseaseService(
            $this->entityManager,
            $this->diseaseConfigRepository,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateDiseaseFromName()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
    }

    public function testHandleDiseaseForCause()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->diseaseConfigRepository
            ->shouldReceive('findByCauses')
            ->andReturn([$diseaseConfig])
            ->twice()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([])
            ->once()
        ;

        $this->playerDiseaseService->handleDiseaseForCause('cause', $player);

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$diseaseConfig])
            ->once()
        ;

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ])->once();

        $this->playerDiseaseService->handleDiseaseForCause('cause', $player);
    }
}
