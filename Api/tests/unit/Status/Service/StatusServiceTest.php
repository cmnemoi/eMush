<?php

namespace Mush\Test\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Repository\StatusRepository;
use Mush\Status\Service\StatusService;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var EventServiceInterface|Mockery\Mock */
    protected EventServiceInterface $eventService;

    /** @var StatusRepository|Mockery\Mock */
    private StatusRepository $repository;

    private StatusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->repository = \Mockery::mock(StatusRepository::class);

        $this->service = new StatusService(
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

    public function testPersist()
    {
        $gameEquipment = new GameItem(new Place());
        $status = new Status($gameEquipment, new StatusConfig());

        $this->entityManager->shouldReceive('persist')->with($status)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->persist($status);
    }

    public function testRemove()
    {
        $gameEquipment = new GameItem(new Place());
        $status = new Status($gameEquipment, new StatusConfig());

        $this->entityManager->shouldReceive('remove')->with($status)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->delete($status);
    }

    public function testGetMostRecent()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $item1 = new GameItem($room);
        $item1->setName('item 1');
        $item2 = new GameItem($room);
        $item2->setName('item 2');
        $item3 = new GameItem($room);
        $item3->setName('item 3');

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::HIDDEN);

        $hidden1 = new Status($item1, $statusConfig);
        $hidden1
            ->setCreatedAt(new \DateTime());

        $hidden2 = new Status($item3, $statusConfig);
        $hidden2
            ->setCreatedAt(new \DateTime());

        $hidden3 = new Status($item2, $statusConfig);
        $hidden3
            ->setCreatedAt(new \DateTime());

        $mostRecent = $this->service->getMostRecent('hidden', new ArrayCollection([$item1, $item2, $item3]));

        $this->assertEquals('item 2', $mostRecent->getName());
    }

    public function testChangeCharge()
    {
        $time = new \DateTime();
        $gameEquipment = new GameItem(new Place());
        $chargeStatusConfig = new ChargeStatusConfig();
        $chargeStatusConfig
            ->setMaxCharge(6)
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
        ;
        $chargeStatus = new ChargeStatus($gameEquipment, $chargeStatusConfig);

        $chargeStatus
            ->setCharge(4)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, -1, [], $time);

        $this->assertEquals(3, $chargeStatus->getCharge());

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, -4, [], $time);

        $this->assertEquals(0, $chargeStatus->getCharge());

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, 7, [], $time);

        $this->assertEquals(6, $chargeStatus->getCharge());

        $chargeStatusConfig->setAutoRemove(true);

        $this->entityManager->shouldReceive('remove')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->eventService->shouldReceive('callEvent')->once();
        $result = $this->service->updateCharge($chargeStatus, -7, [], $time);

        $this->assertNull($result);
    }

    public function testCreateStatusFromConfig()
    {
        $gameEquipment = new GameItem(new Place());
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::MUSH)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->service->createStatusFromConfig($statusConfig, $gameEquipment, [['reason']], new \DateTime());

        $this->assertEquals($result->getOwner(), $gameEquipment);
        $this->assertEquals($result->getName(), PlayerStatusEnum::EUREKA_MOMENT);
        $this->assertEquals($result->getVisibility(), VisibilityEnum::MUSH);
    }

    public function testCreateChargeStatusFromConfig()
    {
        $gameEquipment = new GameItem(new Place());
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setAutoRemove(true)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(3)
            ->setMaxCharge(4)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->service->createStatusFromConfig($statusConfig, $gameEquipment, [['reason']], new \DateTime());

        $this->assertEquals($result->getOwner(), $gameEquipment);
        $this->assertEquals($result->getName(), PlayerStatusEnum::GUARDIAN);
        $this->assertEquals($result->getVisibility(), VisibilityEnum::MUSH);
        $this->assertEquals($result->getThreshold(), 4);
        $this->assertEquals($result->getCharge(), 3);
        $this->assertEquals($result->getChargeVisibility(), VisibilityEnum::PUBLIC);
        $this->assertEquals($result->getStrategy(), ChargeStrategyTypeEnum::CYCLE_INCREMENT);
        $this->assertTrue($result->isAutoRemove());
    }

    public function testCreateAttemptStatus()
    {
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $gameConfig = new GameConfig();
        $gameConfig->addStatusConfig($attemptConfig);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $actionResult = new Fail();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE, $actionResult);

        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals($player->getStatuses()->first()->getName(), StatusEnum::ATTEMPT);
        $this->assertEquals($player->getStatuses()->first()->getCharge(), 1);
        $this->assertEquals($player->getStatuses()->first()->getAction(), ActionEnum::DISASSEMBLE);
    }

    public function testHandleAttemptStatusSameAction()
    {
        $player = new Player();
        $actionResult = new Fail();
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction(ActionEnum::DISASSEMBLE)
            ->setCharge(3)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE, $actionResult);

        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals($player->getStatuses()->first()->getName(), StatusEnum::ATTEMPT);
        $this->assertEquals($player->getStatuses()->first()->getCharge(), 4);
        $this->assertEquals($player->getStatuses()->first()->getAction(), ActionEnum::DISASSEMBLE);
    }

    public function testHandleAttemptStatusNewAction()
    {
        $player = new Player();
        $actionResult = new Fail();
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction(ActionEnum::DISASSEMBLE)
            ->setCharge(3)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->handleAttempt($player, ActionEnum::INSTALL_CAMERA, $actionResult);

        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals($player->getStatuses()->first()->getName(), StatusEnum::ATTEMPT);
        $this->assertEquals($player->getStatuses()->first()->getCharge(), 1);
        $this->assertEquals($player->getStatuses()->first()->getAction(), ActionEnum::INSTALL_CAMERA);
    }

    public function testHandleAttemptStatusSuccess()
    {
        $player = new Player();

        $actionResult = new Success();
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction(ActionEnum::DISASSEMBLE)
            ->setCharge(3)
        ;

        $this->entityManager->shouldReceive('remove')->with($attempt)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE, $actionResult);

        $this->assertCount(0, $player->getStatuses());
    }
}
