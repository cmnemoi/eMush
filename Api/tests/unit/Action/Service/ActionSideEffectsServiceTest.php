<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionSideEffectsService;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;

class ActionSideEffectsServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
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
        $this->eventService = Mockery::mock(EventServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);

        $this->actionService = new ActionSideEffectsService(
            $this->eventService,
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

        $date = new \DateTime();

        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(0, [ModifierScopeEnum::EVENT_DIRTY], ActionEnum::DROP, $date, $player)
            ->andReturn(false)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(0, [ModifierScopeEnum::EVENT_CLUMSINESS], ActionEnum::DROP, $date, $player)
            ->andReturn(false)
            ->twice()
        ;
        $this->eventService->shouldReceive('callEvent')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $this->assertCount(0, $player->getStatuses());

        $action->setDirtyRate(10);

        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(10, [ModifierScopeEnum::EVENT_DIRTY], ActionEnum::DROP, $date, $player)
            ->andReturn(true)
            ->once()
        ;
        $this->eventService
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::DIRTY && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->actionService->handleActionSideEffect($action, $player, $date);
    }

    public function testHandleActionSideEffectDirtyWithApron()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);

        $date = new \DateTime();

        $action
            ->setDirtyRate(100)
            ->setInjuryRate(0)
            ->setName(ActionEnum::DROP)
        ;

        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(100, [ModifierScopeEnum::EVENT_DIRTY], ActionEnum::DROP, $date, $player)
            ->andReturn(false)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(0, [ModifierScopeEnum::EVENT_CLUMSINESS], ActionEnum::DROP, $date, $player)
            ->andReturn(false)
            ->once()
        ;
        $this->eventService->shouldReceive('callEvent')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $this->assertCount(0, $player->getStatuses());
    }

    public function testHandleActionSideEffectInjury()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setPlace($room);
        $date = new \DateTime();

        $action
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setName(ActionEnum::DROP)
        ;

        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(0, [ModifierScopeEnum::EVENT_CLUMSINESS], ActionEnum::DROP, $date, $player)
            ->andReturn(false)
            ->once()
        ;
        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(0, [ModifierScopeEnum::EVENT_DIRTY], ActionEnum::DROP, $date, $player)
            ->andReturn(false)
            ->twice()
        ;
        $this->eventService->shouldReceive('callEvent')->never();

        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $action->setInjuryRate(100)->setName(ActionEnum::DROP);

        $this->modifierService
            ->shouldReceive('isSuccessfulWithModifiers')
            ->with(100, [ModifierScopeEnum::EVENT_CLUMSINESS], ActionEnum::DROP, $date, $player)
            ->andReturn(true)
            ->once()
        ;
        $this->eventService
            ->shouldReceive('dispatch')
            ->withArgs(
                fn (PlayerVariableEvent $playerEvent, string $eventName) => (
                    $playerEvent->getQuantity() === -2 &&
                    $eventName === AbstractQuantityEvent::CHANGE_VARIABLE &&
                    $playerEvent->getModifiedVariable() === PlayerVariableEnum::HEALTH_POINT
                )
            )
            ->once()
        ;
        $player = $this->actionService->handleActionSideEffect($action, $player, $date);

        $this->assertCount(0, $player->getStatuses());
    }
}
