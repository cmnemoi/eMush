<?php

namespace Mush\Tests\unit\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Repository\StatusRepository;
use Mush\Status\Service\StatusService;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class StatusServiceTest extends TestCase
{
    protected EventServiceInterface|Mockery\Mock $eventService;
    private EntityManagerInterface|Mockery\Mock $entityManager;
    private Mockery\Mock|StatusRepository $repository;
    private StatusService $service;

    /**
     * @before
     */
    public function before(): void
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
    public function after(): void
    {
        \Mockery::close();
    }

    public function testGetMostRecent(): void
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

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

        self::assertSame('item 2', $mostRecent->getName());
    }

    public function testChangeCharge(): void
    {
        $time = new \DateTime();
        $place = new Place();
        $place->setDaedalus(new Daedalus());
        $gameEquipment = new GameItem($place);
        $gameEquipment->setName('equipment');

        $chargeStatusConfig = new ChargeStatusConfig();
        $chargeStatusConfig
            ->setMaxCharge(6)
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $chargeStatus = new ChargeStatus($gameEquipment, $chargeStatusConfig);

        $chargeStatus
            ->setCharge(4);

        $this->eventService->shouldReceive('callEvent')->once();
        $this->service->updateCharge($chargeStatus, -1, [], $time);

        $this->eventService->shouldReceive('callEvent')->once();
        $this->service->updateCharge($chargeStatus, -4, [], $time);

        $this->eventService->shouldReceive('callEvent')->once();
        $this->service->updateCharge($chargeStatus, 7, [], $time);

        $chargeStatusConfig->setAutoRemove(true);

        $chargeStatus->setCharge(0);
        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $deleteEvent = new AbstractGameEvent([], new \DateTime());
        $deleteEvent->setPriority(0);
        $this->eventService->shouldReceive('callEvent')->once()->andReturn(new EventChain([$deleteEvent]));
        $this->eventService->shouldReceive('callEvent')->once();
        $result = $this->service->updateCharge($chargeStatus, -7, [], $time);

        self::assertNull($result);
    }

    public function testCreateStatusFromConfig(): void
    {
        $place = new Place();
        $place->setDaedalus(new Daedalus());
        $gameEquipment = new GameItem($place);
        $gameEquipment->setName('equipment');
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::MUSH);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('computeEventModifications')->once()->andReturn(new AbstractGameEvent([], new \DateTime()));

        $result = $this->service->createStatusFromConfig($statusConfig, $gameEquipment, [['reason']], new \DateTime());

        self::assertSame($result->getOwner(), $gameEquipment);
        self::assertSame($result->getName(), PlayerStatusEnum::EUREKA_MOMENT);
        self::assertSame($result->getVisibility(), VisibilityEnum::MUSH);
    }

    public function testCreateChargeStatusFromConfig(): void
    {
        $place = new Place();
        $place->setDaedalus(new Daedalus());
        $gameEquipment = new GameItem($place);
        $gameEquipment->setName('equipment');
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setAutoRemove(true)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(3)
            ->setMaxCharge(4);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('computeEventModifications')->once()->andReturn(new AbstractGameEvent([], new \DateTime()));

        $result = $this->service->createStatusFromConfig($statusConfig, $gameEquipment, [['reason']], new \DateTime());

        self::assertSame($result->getOwner(), $gameEquipment);
        self::assertSame($result->getName(), PlayerStatusEnum::GUARDIAN);
        self::assertSame($result->getVisibility(), VisibilityEnum::MUSH);
        self::assertSame($result->getThreshold(), 4);
        self::assertSame($result->getCharge(), 3);
        self::assertSame($result->getChargeVisibility(), VisibilityEnum::PUBLIC);
        self::assertSame($result->getStrategy(), ChargeStrategyTypeEnum::CYCLE_INCREMENT);
        self::assertTrue($result->isAutoRemove());
    }

    public function testCreateStatusAlreadyHaveStatus(): void
    {
        $place = new Place();
        $place->setDaedalus(new Daedalus());
        $gameEquipment = new GameItem($place);
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::MUSH);

        $status = new Status($gameEquipment, $statusConfig);
        $gameEquipment->addStatus($status);

        $this->entityManager->shouldReceive('persist')->never();
        $this->entityManager->shouldReceive('flush')->never();
        $this->eventService->shouldReceive('callEvent')->never();
        $this->eventService->shouldReceive('computeEventModifications')->never();

        $newStatus = $this->service->createStatusFromConfig($statusConfig, $gameEquipment, [['reason']], new \DateTime());

        self::assertSame($newStatus, $status);
    }

    public function testShouldCreateStatusWithDifferentTargetIfAlreadyExists(): void
    {
        // given a player
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        // given two beds
        $bed1 = GameEquipmentFactory::createEquipmentByName('bed', $player->getPlace());
        $bed2 = GameEquipmentFactory::createEquipmentByName('bed', $player->getPlace());

        // given this player has a lying down status on bed1
        $lyingDownStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::LYING_DOWN, $player);
        $lyingDownStatus->setTarget($bed1);

        // setup universe state
        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('computeEventModifications')->once()->andReturn(new AbstractGameEvent([], new \DateTime()));

        // when the player tries to lie down on bed2
        $newStatus = $this->service->createStatusFromConfig(
            $lyingDownStatus->getStatusConfig(),
            $player,
            [],
            new \DateTime(),
            $bed2
        );

        // then a new status should be created for the Plasma Shield project
        self::assertNotSame($newStatus, $lyingDownStatus);
    }

    public function testHandleAttemptStatusOnFail(): void
    {
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $gameConfig = new GameConfig();
        $gameConfig->addStatusConfig($attemptConfig);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = new Player();
        $player->setDaedalus($daedalus);

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('player');
        new PlayerInfo($player, new User(), $characterConfig);

        $actionResult = new Fail();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE->value, $actionResult, [], new \DateTime());

        self::assertCount(1, $player->getStatuses());
        self::assertSame($player->getStatuses()->first()->getName(), StatusEnum::ATTEMPT);
        self::assertSame($player->getStatuses()->first()->getCharge(), 0);
        self::assertSame($player->getStatuses()->first()->getAction(), ActionEnum::DISASSEMBLE->value);
    }

    public function testHandleAttemptStatusSameAction(): void
    {
        $player = new Player();
        $player->setDaedalus(new Daedalus());

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('player');
        new PlayerInfo($player, new User(), $characterConfig);

        $actionResult = new Fail();
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction(ActionEnum::DISASSEMBLE->value)
            ->setCharge(3);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE->value, $actionResult, [], new \DateTime());

        self::assertCount(1, $player->getStatuses());
        self::assertSame($player->getStatuses()->first()->getName(), StatusEnum::ATTEMPT);
        self::assertSame($player->getStatuses()->first()->getCharge(), 3);
        self::assertSame($player->getStatuses()->first()->getAction(), ActionEnum::DISASSEMBLE->value);
    }

    public function testHandleAttemptStatusNewAction(): void
    {
        $player = new Player();
        $player->setDaedalus(new Daedalus());
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('player');
        new PlayerInfo($player, new User(), $characterConfig);

        $actionResult = new Fail();
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction(ActionEnum::DISASSEMBLE->value)
            ->setCharge(3);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->handleAttempt($player, ActionEnum::INSTALL_CAMERA->value, $actionResult, [], new \DateTime());

        self::assertCount(1, $player->getStatuses());
        self::assertSame($player->getStatuses()->first()->getName(), StatusEnum::ATTEMPT);
        self::assertSame($player->getStatuses()->first()->getCharge(), 0);
        self::assertSame($player->getStatuses()->first()->getAction(), ActionEnum::INSTALL_CAMERA->value);
    }

    public function testHandleAttemptStatusSuccess(): void
    {
        $player = new Player();
        $player->setDaedalus(new Daedalus());
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('player');
        new PlayerInfo($player, new User(), $characterConfig);

        $actionResult = new Success();
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName(StatusEnum::ATTEMPT);

        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction(ActionEnum::DISASSEMBLE->value)
            ->setCharge(3);

        $this->entityManager->shouldReceive('remove')->with($attempt)->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->service->handleAttempt($player, ActionEnum::DISASSEMBLE->value, $actionResult, [], new \DateTime());

        self::assertCount(0, $player->getStatuses());
    }

    public function testCreateContentStatusFromConfig(): void
    {
        $place = new Place();
        $place->setDaedalus(new Daedalus());

        $gameEquipment = new GameItem($place);
        $gameEquipment->setName('equipment');
        $statusConfig = new ContentStatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::MUSH);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('computeEventModifications')->once()->andReturn(new AbstractGameEvent([], new \DateTime()));

        $result = $this->service->createStatusFromConfig($statusConfig, $gameEquipment, [['reason']], new \DateTime());
        $result->setContent('test content');

        self::assertSame($result->getOwner(), $gameEquipment);
        self::assertSame($result->getName(), PlayerStatusEnum::GUARDIAN);
        self::assertSame($result->getVisibility(), VisibilityEnum::MUSH);
        self::assertSame($result->getContent(), 'test content');
    }
}
