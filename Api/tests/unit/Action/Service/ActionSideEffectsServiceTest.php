<?php

namespace unit\Action\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionSideEffectsService;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionSideEffectsServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;

    private ActionSideEffectsServiceInterface $actionService;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->actionService = new ActionSideEffectsService(
            $this->eventDispatcher,
            $this->randomService,
            $this->roomLogService,
            $this->modifierService
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
            ->setName(ActionEnum::DROP)
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());

        $action->setDirtyRate(10);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::EVENT_DIRTY], ModifierTargetEnum::PERCENTAGE, 10, ActionEnum::DROP)
            ->andReturn(100)
        ;
        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::DIRTY && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->actionService->handleActionSideEffect($action, $player, new \DateTime());
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
            ->setName(ActionEnum::DROP)
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

        $player->addEquipment($gameItem);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::EVENT_DIRTY], ModifierTargetEnum::PERCENTAGE, 100, ActionEnum::DROP)
            ->andReturn(0);
        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->roomLogService->shouldReceive('createLog')->once();
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

        $action->setInjuryRate(100)->setName(ActionEnum::DROP);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(
                fn (PlayerModifierEvent $playerEvent, string $eventName) => ($playerEvent->getQuantity() === -2 && $eventName === PlayerModifierEvent::HEALTH_POINT_MODIFIER)
            )
            ->once()
        ;

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::EVENT_CLUMSINESS], ModifierTargetEnum::PERCENTAGE, 100, ActionEnum::DROP)
            ->andReturn(100)
        ;

        $this->roomLogService->shouldReceive('createLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;
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
            ->setName(ActionEnum::DROP)
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

        $player->addEquipment($gameItem);

        $this->modifierService
            ->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::EVENT_CLUMSINESS], ModifierTargetEnum::PERCENTAGE, 100, ActionEnum::DROP)
            ->andReturn(0)
        ;
        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->roomLogService->shouldReceive('createLog')->once();
        $this->randomService->shouldReceive('randomPercent')->andReturn(10)->once();
        $player = $this->actionService->handleActionSideEffect($action, $player, new \DateTime());

        $this->assertCount(0, $player->getStatuses());
    }

    private function createGear(string $target, float $delta, string $scope, string $reach): Gear
    {
        $modifier = new ModifierConfig();
        $modifier
            ->setTarget($target)
            ->setDelta($delta)
            ->setScope($scope)
            ->setReach($reach)
        ;

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifier]));

        return $gear;
    }
}
