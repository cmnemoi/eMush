<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class DiseaseCauseServiceTest extends TestCase
{
    private DiseaseCauseService $diseaseCauseService;

    /** @var PlayerDiseaseService|Mockery\Mock */
    private PlayerDiseaseService $playerDiseaseService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var ConsumableDiseaseServiceInterface|Mockery\Mock */
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    /**
     * @before
     */
    public function before()
    {
        $this->playerDiseaseService = \Mockery::mock(PlayerDiseaseService::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->consumableDiseaseService = \Mockery::mock(ConsumableDiseaseServiceInterface::class);

        $this->diseaseCauseService = new DiseaseCauseService(
            $this->playerDiseaseService,
            $this->randomService,
            $this->consumableDiseaseService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testSpoiledFoodHazardous()
    {
        $diseaseName = 'name';

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases([$diseaseName => 1])
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD)
        ;

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseCauseConfig($diseaseCauseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment(new Place());

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->never()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::HAZARDOUS);
        $hazardous = new Status($gameEquipment, $statusConfig);

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

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('name', $probaCollection->toArray()))
                && $probaCollection->toArray()['name'] === 1
            )
            ->andReturn($diseaseName)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->with($diseaseName, $player, [DiseaseCauseEnum::PERISHED_FOOD], null, null)
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }

    public function testSpoiledFoodDecomposing()
    {
        $diseaseName = 'name';

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases([$diseaseName => 1])
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD)
        ;

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseCauseConfig($diseaseCauseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment(new Place());

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->never()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::DECOMPOSING);
        $hazardous = new Status($gameEquipment, $statusConfig);

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

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('name', $probaCollection->toArray()))
                && $probaCollection->toArray()['name'] === 1
            )
            ->andReturn($diseaseName)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->with($diseaseName, $player, [DiseaseCauseEnum::PERISHED_FOOD], null, null)
            ->once()
        ;

        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);
    }

    public function testConsumableWithDiseases()
    {
        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment(new Place());
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
        $gameEquipment = new GameEquipment(new Place());
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
        $diseaseConfig->setDiseaseName($diseaseName);

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
        ;

        $player->addMedicalCondition($playerDisease);

        $this->playerDiseaseService
            ->shouldReceive('removePlayerDisease')
            ->once()
        ;

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);
    }

    public function testHandleDiseaseForCause()
    {
        $diseaseName = 'name';

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases([$diseaseName => 1])
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD)
        ;

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseCauseConfig($diseaseCauseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('name', $probaCollection->toArray()))
                && $probaCollection->toArray()['name'] === 1
            )
            ->andReturn($diseaseName)
            ->once()
        ;

        $this->playerDiseaseService
            ->shouldReceive('createDiseaseFromName')
            ->with($diseaseName, $player, [DiseaseCauseEnum::PERISHED_FOOD], null, null)
            ->once()
        ;

        $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);
    }
}
