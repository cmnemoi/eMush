<?php

namespace Mush\Test\Place\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Event\PlaceInitEvent;
use Mush\Place\Repository\PlaceRepository;
use Mush\Place\Service\PlaceService;
use Mush\Place\Service\PlaceServiceInterface;
use PHPUnit\Framework\TestCase;

class PlaceServiceTest extends TestCase
{
    private PlaceServiceInterface $placeService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var PlaceRepository|Mockery\Mock */
    private PlaceRepository $repository;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->repository = \Mockery::mock(PlaceRepository::class);

        $this->placeService = new PlaceService(
            $this->entityManager,
            $this->eventService,
            $this->repository,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreateRoom()
    {
        $daedalus = new Daedalus();

        $gameConfig = new GameConfig();
        $daedalusConfig = new DaedalusConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $equipmentConfigCollection = new ArrayCollection();
        $equipmentConfigCollection->add($this->createEquipmentConfig(EquipmentEnum::DOOR));
        $equipmentConfigCollection->add($this->createEquipmentConfig(EquipmentEnum::COMMUNICATION_CENTER));
        $equipmentConfigCollection->add($this->createEquipmentConfig(ItemEnum::BLASTER));

        $gameConfig->setEquipmentsConfig($equipmentConfigCollection);

        $roomConfig = $this->createRoomConfig('bridge', $daedalusConfig);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (PlaceInitEvent $event) => (
                $event->getPlaceConfig() === $roomConfig)
            )
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $result = $this->placeService->createPlace($roomConfig, $daedalus, ['daedalus_start'], new \DateTime());

        $this->assertInstanceOf(Place::class, $result);
        $this->assertCount(0, $result->getDoors());
        $this->assertCount(0, $result->getEquipments());
    }

    private function createRoomConfig(string $name, DaedalusConfig $daedalusConfig): PlaceConfig
    {
        $roomConfig = new PlaceConfig();

        $roomConfig
            ->setPlaceName($name)
            ->setDoors([
                DoorEnum::BRIDGE_FRONT_ALPHA_TURRET,
                DoorEnum::BRIDGE_FRONT_BRAVO_TURRET,
                DoorEnum::FRONT_CORRIDOR_BRIDGE,
            ])
            ->setEquipments([
                EquipmentEnum::COMMUNICATION_CENTER,
            ])
            ->setItems([
                ItemEnum::BLASTER,
            ])
        ;

        return $roomConfig;
    }

    protected function createEquipmentConfig(string $name): EquipmentConfig
    {
        $equipmentConfig = new EquipmentConfig();

        $equipmentConfig
            ->setEquipmentName($name)
        ;

        return $equipmentConfig;
    }
}
