<?php

namespace Mush\Test\Status\Service;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Repository\StatusConfigRepository;
use Mush\Status\Repository\StatusRepository;
use Mush\Status\Service\StatusService;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var StatusRepository|Mockery\Mock */
    private StatusRepository $repository;

    /** @var StatusConfigRepository|Mockery\Mock */
    private StatusConfigRepository $configRepository;

    private StatusService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->repository = Mockery::mock(StatusRepository::class);
        $this->configRepository = Mockery::mock(StatusConfigRepository::class);

        $this->service = new StatusService(
            $this->entityManager,
            $this->repository,
            $this->configRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testPersist()
    {
        $gameEquipment = new GameItem();
        $status = new Status($gameEquipment);

        $this->entityManager->shouldReceive('persist')->with($status)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->persist($status);
    }

    public function testRemove()
    {
        $gameEquipment = new GameItem();
        $status = new Status($gameEquipment);

        $this->entityManager->shouldReceive('remove')->with($status)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->delete($status);
    }

    public function testGetMostRecent()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $item1 = new GameItem();
        $item1->setHolder($room)->setName('item 1');
        $item2 = new GameItem();
        $item2->setHolder($room)->setName('item 2');
        $item3 = new GameItem();
        $item3->setHolder($room)->setName('item 3');

        $hidden1 = new Status($item1);
        $hidden1
            ->setName('hidden')
            ->setCreatedAt(new DateTime());

        $hidden2 = new Status($item3);
        $hidden2
            ->setName('hidden')
            ->setCreatedAt(new DateTime());

        $hidden3 = new Status($item2);
        $hidden3
            ->setName('hidden')
            ->setCreatedAt(new DateTime());

        $mostRecent = $this->service->getMostRecent('hidden', new ArrayCollection([$item1, $item2, $item3]));

        $this->assertEquals('item 2', $mostRecent->getName());
    }

    public function testChangeCharge()
    {
        $gameEquipment = new GameItem();
        $chargeStatus = new ChargeStatus($gameEquipment);

        $chargeStatus
            ->setCharge(4)
            ->setThreshold(6)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, -1);

        $this->assertEquals(3, $chargeStatus->getCharge());

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, -4);

        $this->assertEquals(0, $chargeStatus->getCharge());

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->updateCharge($chargeStatus, 7);

        $this->assertEquals(6, $chargeStatus->getCharge());

        $chargeStatus->setAutoRemove(true);

        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $result = $this->service->updateCharge($chargeStatus, -7);

        $this->assertNull($result);
    }

    public function testCreateStatusFromConfig()
    {
        $gameEquipment = new GameItem();
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::MUSH)
        ;

        $result = $this->service->createStatusFromConfig($statusConfig, $gameEquipment);

        $this->assertEquals($result->getOwner(), $gameEquipment);
        $this->assertEquals($result->getName(), PlayerStatusEnum::EUREKA_MOMENT);
        $this->assertEquals($result->getVisibility(), VisibilityEnum::MUSH);
    }

    public function testCreateChargeStatusFromConfig()
    {
        $gameEquipment = new GameItem();
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setAutoRemove(true)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
        ;

        $result = $this->service->createChargeStatusFromConfig($statusConfig, $gameEquipment, 3, 4);

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
        $player = new Player();
        $actionResult = new Fail();

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
        $attempt = new Attempt($player);
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction(ActionEnum::DISASSEMBLE)
            ->setCharge(3)
        ;

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
        $attempt = new Attempt($player);
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction(ActionEnum::DISASSEMBLE)
            ->setCharge(3)
        ;

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
        $attempt = new Attempt($player);
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction(ActionEnum::DISASSEMBLE)
            ->setCharge(3)
        ;

        $this->entityManager->shouldReceive('remove')->with($attempt)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE, $actionResult);

        $this->assertCount(0, $player->getStatuses());
    }
}
