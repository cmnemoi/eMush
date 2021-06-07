<?php

namespace Mush\Test\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DiseaseCauseServiceTest extends TestCase
{
    private DiseaseCauseService $diseaseCauseService;

    /** @var PlayerDiseaseService | Mockery\Mock */
    private PlayerDiseaseService $playerDiseaseService;

    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var ConsumableDiseaseServiceInterface | Mockery\Mock */
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @before
     */
    public function before()
    {
        $this->playerDiseaseService = Mockery::mock(PlayerDiseaseService::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->consumableDiseaseService = Mockery::mock(ConsumableDiseaseServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->diseaseCauseService = new DiseaseCauseService(
            $this->playerDiseaseService,
            $this->randomService,
            $this->consumableDiseaseService,
            $this->eventDispatcher
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testSpoiledFoodHazardous()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment();

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->never()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $hazardous = new Status($gameEquipment);
        $hazardous->setName(EquipmentStatusEnum::HAZARDOUS);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }

    public function testSpoiledFoodDecomposing()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment();

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->never()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $hazardous = new Status($gameEquipment);
        $hazardous->setName(EquipmentStatusEnum::DECOMPOSING);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('handleDiseaseForCause')
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }

    public function testConsumableWithDiseases()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment();
        $gameEquipment->setName('someName');

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn(null)
            ->once()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        $disease = new ConsumableDiseaseAttribute();
        $disease->setDisease('disease name');

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDiseasesAttribute(new ArrayCollection([$disease]))
        ;

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn($consumableDisease)
            ->twice()
        ;

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig(new DiseaseConfig())
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->andReturn($playerDisease)
            ->once()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);
    }

    public function testConsumableWithCures()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseName = 'someName';
        $gameEquipment = new GameEquipment();
        $gameEquipment->setName($diseaseName);

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn(null)
            ->once()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        $cure = new ConsumableDiseaseAttribute();
        $cure
            ->setType(TypeEnum::CURE)
            ->setDisease($diseaseName)
        ;

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDiseasesAttribute(new ArrayCollection([$cure]))
        ;

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn($consumableDisease)
            ->twice()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setName($diseaseName);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
        ;

        $player->addDisease($playerDisease);

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerDiseaseService
            ->shouldReceive('delete')
            ->once()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);
    }
}
