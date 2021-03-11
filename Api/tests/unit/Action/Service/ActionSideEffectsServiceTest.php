<?php

namespace unit\Action\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Service\ActionSideEffectsService;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionSideEffectsServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var ActionModifierServiceInterface | Mockery\Mock */
    private ActionModifierServiceInterface $actionModifierService;

    private ActionSideEffectsServiceInterface $actionService;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->actionModifierService = Mockery::mock(ActionModifierServiceInterface::class);

        $this->actionService = new ActionSideEffectsService(
            $this->eventDispatcher,
            $this->randomService,
            $this->statusService,
            $this->roomLogService,
            $this->actionModifierService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testHandleActionSideEffectDirty()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(0)
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());

        $action->setDirtyRate(100);

        $this->actionModifierService->shouldReceive('getModifiedValue')->andReturn(100);
        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $this->statusService->shouldReceive('createCoreStatus')->andReturn(new Status($player))->once();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(1, $player->getStatuses());
    }

    public function testHandleActionSideEffectDirtyWithApron()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
        ;

        $itemConfig = new ItemConfig();

        $apronGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            -100,
            ModifierScopeEnum::EVENT_DIRTY,
            ReachEnum::INVENTORY
        );

        $itemConfig->setMechanics(new ArrayCollection([$apronGear]));
        $gameItem = new GameItem();
        $gameItem->setEquipment($itemConfig);

        $player->addItem($gameItem);

        $this->actionModifierService->shouldReceive('getModifiedValue')->andReturn(0);
        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());
    }

    public function testHandleActionSideEffectInjury()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(0)
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());

        $action->setInjuryRate(100);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(
                fn (PlayerEvent $playerEvent, string $eventName) => ((int) $playerEvent->getModifier()->getDelta() === -2)
            )
            ->once()
        ;

        $this->actionModifierService->shouldReceive('getModifiedValue')->andReturn(100);
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $this->statusService->shouldReceive('createCorePlayerStatus')->never();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());
    }

    public function testHandleActionSideEffectInjuryWithGloves()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(100)
        ;

        $itemConfig = new ItemConfig();

        $apronGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            -100,
            ModifierScopeEnum::EVENT_CLUMSINESS,
            ReachEnum::INVENTORY
        );

        $itemConfig->setMechanics(new ArrayCollection([$apronGear]));
        $gameItem = new GameItem();
        $gameItem->setEquipment($itemConfig);

        $player->addItem($gameItem);

        $this->actionModifierService->shouldReceive('getModifiedValue')->andReturn(0);
        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->roomLogService->shouldReceive('createPlayerLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());
    }

    private function createGear(string $target, float $delta, string $scope, string $reach): Gear
    {
        $modifier = new Modifier();
        $modifier
            ->setTarget($target)
            ->setDelta($delta)
            ->setScope($scope)
            ->setReach($reach)
        ;

        $gear = new Gear();
        $gear->setModifier(new ArrayCollection([$modifier]));

        return $gear;
    }
}
