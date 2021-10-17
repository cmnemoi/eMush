<?php

namespace Mush\Test\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Config\Mechanics\Document;
use Mush\Equipment\Entity\Config\Mechanics\Plant;
use Mush\Equipment\Entity\Config\PlantEffect;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
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
            ->setMechanics(new ArrayCollection([]))
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::ALIEN_ARTEFACT);
        $itemConfig->setInitStatus(new ArrayCollection([$statusConfig]));

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::ALIEN_ARTEFACT)
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('alien Artifact', $gameItem->getName());

        //Heavy
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('heavy item')
            ->setMechanics(new ArrayCollection([]))
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::HEAVY);
        $itemConfig->setInitStatus(new ArrayCollection([$statusConfig]));

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::HEAVY)
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
            ->setMechanics(new ArrayCollection([$plantMechanic]))
        ;

        $plantEffect = new PlantEffect();
        $plantEffect->setMaturationTime(8);

        $this->equipmentEffectService
            ->shouldReceive('getPlantEffect')
            ->with($plantMechanic, $daedalus)
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

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::PLANT_YOUNG)
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

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $daedalus);

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some document', $gameItem->getName());
    }
}
