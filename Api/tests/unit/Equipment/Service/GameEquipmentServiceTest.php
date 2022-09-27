<?php

namespace Mush\Test\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Event\Service\EventServiceInterface;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;

class GameEquipmentServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var GameEquipmentRepository|Mockery\Mock */
    private GameEquipmentRepository $repository;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EquipmentServiceInterface|Mockery\Mock */
    private EquipmentServiceInterface $equipmentService;

    private GameEquipmentService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher = Mockery::mock(EventServiceInterface::class);
        $this->repository = Mockery::mock(GameEquipmentRepository::class);
        $this->equipmentService = Mockery::mock(EquipmentServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new GameEquipmentService(
            $this->entityManager,
            $this->repository,
            $this->equipmentService,
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

    public function testCreateBasicItem()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $place->setDaedalus($daedalus);

        // Basic item
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some Name')
            ->setMechanics(new ArrayCollection([]));

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (EquipmentInitEvent $event) => (
                $event->getEquipmentConfig() === $itemConfig)
            )
            ->once()
        ;
        $this->entityManager
            ->shouldReceive('persist')
            ->once();
        $this->entityManager
            ->shouldReceive('flush')
            ->once();

        $gameItem = $this->service->createGameEquipment($itemConfig, $place, 'reason', new \DateTime());

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some Name', $gameItem->getName());
    }

    public function testCreateBasicEquipment()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $place->setDaedalus($daedalus);

        // Equipment
        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig
            ->setName('equipment Name')
            ->setMechanics(new ArrayCollection([]))
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (EquipmentInitEvent $event) => (
                $event->getEquipmentConfig() === $equipmentConfig)
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

        $gameEquipment = $this->service->createGameEquipment($equipmentConfig, $place, 'reason', new \DateTime());

        $this->assertInstanceOf(GameEquipment::class, $gameEquipment);
        $this->assertEquals('equipment Name', $gameEquipment->getName());
    }

    public function testCreatePlant()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $place->setDaedalus($daedalus);

        // Plant
        $plantMechanic = new Plant();
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some plant')
            ->setMechanics(new ArrayCollection([$plantMechanic]))
        ;

        $plantEffect = new PlantEffect();
        $plantEffect->setMaturationTime(8);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => (
                $event instanceof EquipmentInitEvent &&
                $event->getEquipmentConfig() === $itemConfig)
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

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => (
                $event instanceof StatusEvent &&
                $event->getStatusName() === EquipmentStatusEnum::PLANT_YOUNG))
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $place, 'reason', new \DateTime());

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some plant', $gameItem->getName());
    }

    public function testCreateDocument()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $place->setDaedalus($daedalus);

        $documentMechanic = new Document();
        $documentMechanic->setContent('Hello world');

        $itemConfig = new ItemConfig();
        $itemConfig
            ->setName('some document')
            ->setMechanics(new ArrayCollection([$documentMechanic]))
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => (
                $event instanceof EquipmentInitEvent &&
                $event->getEquipmentConfig() === $itemConfig)
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

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => (
                $event instanceof StatusEvent &&
                $event->getStatusName() === EquipmentStatusEnum::DOCUMENT_CONTENT))
            ->once()
        ;

        $gameItem = $this->service->createGameEquipment($itemConfig, $place, 'reason', new \DateTime());

        $this->assertInstanceOf(GameItem::class, $gameItem);
        $this->assertEquals('some document', $gameItem->getName());
    }
}
