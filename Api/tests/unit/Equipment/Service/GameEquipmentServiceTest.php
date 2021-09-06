<?php

namespace Mush\Test\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameEquipmentServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameEquipmentRepository|Mockery\Mock */
    private GameEquipmentRepository $repository;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EquipmentServiceInterface|Mockery\Mock */
    private EquipmentServiceInterface $equipmentService;
    /** @var EquipmentEffectServiceInterface|Mockery\Mock */
    private EquipmentEffectServiceInterface $equipmentEffectService;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    private GameEquipmentService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->repository = Mockery::mock(GameEquipmentRepository::class);
        $this->equipmentService = Mockery::mock(EquipmentServiceInterface::class);
        $this->equipmentEffectService = Mockery::mock(EquipmentEffectServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);

        $this->service = new GameEquipmentService(
            $this->entityManager,
            $this->repository,
            $this->equipmentService,
            $this->statusService,
            $this->equipmentEffectService,
            $this->randomService,
            $this->eventDispatcher,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateGameEquipment()
    {
        $daedalus = new Daedalus();

        //Basic item
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some Name')
            ->setIsAlienArtifact(false)
            ->setIsHeavy(false)
            ->setMechanics(new ArrayCollection([]))
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some Name', $gameItem->getName());

        //Equipment
        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig
            ->setName('equipment Name')
            ->setIsAlienArtifact(false)
            ->setMechanics(new ArrayCollection([]))
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $gameEquipment = $this->service->createGameEquipment($equipmentConfig, $daedalus);

        $this->assertInstanceOf(GameEquipment::class, $gameEquipment);
        $this->assertEquals('equipment Name', $gameEquipment->getName());

        //Alien Artifact
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('alien Artifact')
            ->setIsAlienArtifact(true)
            ->setIsHeavy(false)
            ->setMechanics(new ArrayCollection([]))
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->andReturn(new Status($gameItem))
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('alien Artifact', $gameItem->getName());

        //Heavy
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('heavy item')
            ->setIsAlienArtifact(false)
            ->setIsHeavy(true)
            ->setMechanics(new ArrayCollection([]))
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->andReturn(new Status($gameItem))
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('heavy item', $gameItem->getName());

        //Plant
        $plantMechanic = new Plant();
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some plant')
            ->setIsAlienArtifact(false)
            ->setIsHeavy(false)
            ->setMechanics(new ArrayCollection([$plantMechanic]))
        ;

        $plantEffect = new PlantEffect();
        $plantEffect->setMaturationTime(8);

        $this->equipmentEffectService
            ->shouldReceive('getPlantEffect')
            ->andReturn($plantEffect)
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

        $this->statusService
            ->shouldReceive('createChargeStatus')
            ->andReturn(new ChargeStatus($gameItem))
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some plant', $gameItem->getName());

        //Document
        $documentMechanic = new Document();
        $documentMechanic->setContent('Hello world');

        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some document')
            ->setIsAlienArtifact(false)
            ->setIsHeavy(false)
            ->setMechanics(new ArrayCollection([$documentMechanic]))
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertCount(1, $gameItem->getStatuses());
        $this->assertEquals('some document', $gameItem->getName());
        $this->assertEquals('Hello world', $gameItem->getStatuses()->first()->getContent());

        //Charged
        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(4)
            ->setStartCharge(2)
            ->setIsVisible(true)
            ->setChargeStrategy('charge startegy')
        ;

        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some charged')
            ->setIsAlienArtifact(false)
            ->setIsHeavy(false)
            ->setMechanics(new ArrayCollection([$chargedMechanic]))
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $this->statusService
            ->shouldReceive('createChargeStatus')
            ->andReturn(new ChargeStatus($gameItem))
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some charged', $gameItem->getName());
    }
}
