<?php

namespace Mush\Test\Place\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\gameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Repository\PlaceRepository;
use Mush\Place\Service\PlaceService;
use Mush\Place\Service\PlaceServiceInterface;
use PHPUnit\Framework\TestCase;

class PlaceServiceTest extends TestCase
{
    private PlaceServiceInterface $placeService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var gameEquipmentServiceInterface|Mockery\Mock */
    private gameEquipmentServiceInterface $equipmentService;
    /** @var PlaceRepository|Mockery\Mock */
    private PlaceRepository $repository;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(PlaceRepository::class);
        $this->equipmentService = Mockery::mock(gameEquipmentServiceInterface::class);

        $this->placeService = new PlaceService(
            $this->entityManager,
            $this->repository,
            $this->equipmentService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateRoom()
    {
        $daedalus = new Daedalus();

        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus->setGameConfig($gameConfig);

        $equipmentConfigCollection = new ArrayCollection();
        $equipmentConfigCollection->add($this->createEquipmentConfig(EquipmentEnum::DOOR));
        $equipmentConfigCollection->add($this->createEquipmentConfig(EquipmentEnum::COMMUNICATION_CENTER));
        $equipmentConfigCollection->add($this->createEquipmentConfig(ItemEnum::TABULATRIX));

        $gameConfig->setEquipmentsConfig($equipmentConfigCollection);

        $roomConfig = $this->createRoomConfig('bridge', $daedalusConfig);

        $this->equipmentService
            ->shouldReceive('createGameEquipment')
            ->andReturn(new GameEquipment())
            ->once()
        ;
        $this->equipmentService
            ->shouldReceive('createGameEquipment')
            ->andReturn(new GameItem())
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->twice()
        ;

        $result = $this->placeService->createPlace($roomConfig, $daedalus);

        $this->assertInstanceOf(Place::class, $result);
        $this->assertCount(3, $result->getDoors());
        $this->assertCount(5, $result->getEquipments());

        //create the room on the other side of the doors
        $daedalus->addPlace($result);
        $roomConfig = $this->createRoomConfig('bridge2', $daedalusConfig);

        $this->equipmentService
            ->shouldReceive('createGameEquipment')
            ->andReturn(new GameEquipment())
            ->once()
        ;
        $this->equipmentService
            ->shouldReceive('createGameEquipment')
            ->andReturn(new GameItem())
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->twice()
        ;

        $return = $this->placeService->createPlace($roomConfig, $daedalus);

        $this->assertInstanceOf(Place::class, $return);
        $this->assertCount(3, $return->getDoors());
        $this->assertCount(2, $return->getEquipments());
        $this->assertCount(2, $return->getDoors()->first()->getRooms());
        $this->assertEquals($daedalus->getRooms()->first(), $return->getDoors()->first()->getRooms()->first());
    }

    private function createRoomConfig(string $name, DaedalusConfig $daedalusConfig): PlaceConfig
    {
        $roomConfig = new PlaceConfig();

        $roomConfig
            ->setDaedalusConfig($daedalusConfig)
            ->setName($name)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum::BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_BRIDGE,
            ])
            ->setEquipments([
                EquipmentEnum::COMMUNICATION_CENTER,
            ])
            ->setItems([
                ItemEnum::TABULATRIX,
            ])
        ;

        return $roomConfig;
    }

    protected function createEquipmentConfig(string $name): EquipmentConfig
    {
        $equipmentConfig = new EquipmentConfig();

        $equipmentConfig
            ->setName($name)
        ;

        return $equipmentConfig;
    }
}
