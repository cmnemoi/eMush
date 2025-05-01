<?php

namespace Mush\Tests\unit\Equipment\Service;

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
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\DamageEquipmentServiceInterface;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GameEquipmentServiceTest extends TestCase
{
    private DamageEquipmentServiceInterface|Mockery\Mock $damageEquipmentService;
    private EventServiceInterface|Mockery\Mock $eventService;
    private EntityManagerInterface|Mockery\Mock $entityManager;
    private GameEquipmentRepository|Mockery\Mock $repository;
    private Mockery\Mock|RandomServiceInterface $randomService;
    private EquipmentServiceInterface|Mockery\Mock $equipmentService;
    private EquipmentEffectServiceInterface|Mockery\Mock $equipmentEffectService;
    private Mockery\Mock|StatusServiceInterface $statusService;

    private GameEquipmentService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->damageEquipmentService = \Mockery::mock(DamageEquipmentServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->repository = \Mockery::mock(GameEquipmentRepository::class);
        $this->equipmentService = \Mockery::mock(EquipmentServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->equipmentEffectService = \Mockery::mock(EquipmentEffectServiceInterface::class);

        $this->service = new GameEquipmentService(
            $this->entityManager,
            $this->repository,
            $this->damageEquipmentService,
            $this->equipmentService,
            $this->randomService,
            $this->eventService,
            $this->statusService,
            $this->equipmentEffectService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreateBasicItem()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $place->setDaedalus($daedalus);

        // Basic item
        $itemConfig = new ItemConfig();
        $itemConfig
            ->setEquipmentName('some Name')
            ->setMechanics(new ArrayCollection([]));

        $this->repository->shouldReceive('save')->once();

        $this->eventService->shouldReceive('callEvent')->once();
        $gameItem = $this->service->createGameEquipment(
            $itemConfig,
            $place,
            ['reason'],
            new \DateTime()
        );

        self::assertInstanceOf(GameItem::class, $gameItem);
        self::assertSame('some Name', $gameItem->getName());
    }

    public function testCreateBasicEquipment()
    {
        $daedalus = new Daedalus();
        $place = new Place();
        $place->setDaedalus($daedalus);

        // Equipment
        $equipmentConfig = new EquipmentConfig();
        $equipmentConfig
            ->setEquipmentName('equipment Name')
            ->setMechanics(new ArrayCollection([]));

        $this->repository->shouldReceive('save')->once();

        $this->eventService->shouldReceive('callEvent')->once();
        $gameEquipment = $this->service->createGameEquipment(
            $equipmentConfig,
            $place,
            ['reason'],
            new \DateTime()
        );

        self::assertInstanceOf(GameEquipment::class, $gameEquipment);
        self::assertSame('equipment Name', $gameEquipment->getName());
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
            ->setEquipmentName('some plant')
            ->setMechanics(new ArrayCollection([$plantMechanic]));

        $plantEffect = new PlantEffect();
        $plantEffect->setMaturationTime(8);

        $this->repository->shouldReceive('save')->once();

        $this->equipmentEffectService
            ->shouldReceive('getPlantEffect')
            ->with($plantMechanic, $daedalus)
            ->andReturn($plantEffect)
            ->once();

        $status = \Mockery::mock(ChargeStatus::class);
        $this->statusService
            ->shouldReceive('createStatusFromName')
            ->andReturn($status)
            ->once();
        $chargeVariable = \Mockery::mock(GameVariable::class);
        $status->shouldReceive('getVariableByName')->andReturn($chargeVariable);
        $chargeVariable->shouldReceive('setMaxValue')->with(8);
        $this->statusService->shouldReceive('persist')->once();

        $this->eventService->shouldReceive('callEvent')->once();
        $gameItem = $this->service->createGameEquipment(
            $itemConfig,
            $place,
            ['reason'],
            new \DateTime()
        );

        self::assertInstanceOf(GameItem::class, $gameItem);
        self::assertSame('some plant', $gameItem->getName());
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
            ->setEquipmentName('some document')
            ->setMechanics(new ArrayCollection([$documentMechanic]));

        $gameEquipment = new GameItem($place);
        $gameEquipment
            ->setName('some document')
            ->setEquipment($itemConfig);

        $statusConfig = new ContentStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::DOCUMENT_CONTENT);

        $status = new ContentStatus($gameEquipment, $statusConfig);
        $status->setContent($documentMechanic->getContent());

        $this->repository->shouldReceive('save')->once();

        $this->eventService->shouldReceive('callEvent')->once();

        $this->statusService
            ->shouldReceive('createStatusFromName')->andReturn($status)->once();

        $gameItem = $this->service->createGameEquipment(
            $itemConfig,
            $place,
            ['reason'],
            new \DateTime()
        );

        self::assertInstanceOf(GameItem::class, $gameItem);
        self::assertSame('some document', $gameItem->getName());
    }
}
