<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionSideEffectsService;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;

class ActionSideEffectsServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var EventModifierServiceInterface|Mockery\Mock */
    private EventModifierServiceInterface $modifierService;

    private ActionSideEffectsServiceInterface $actionService;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->modifierService = \Mockery::mock(EventModifierServiceInterface::class);

        $this->actionService = new ActionSideEffectsService(
            $this->eventService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testHandleActionSideEffect()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setDaedalus(new Daedalus());
        $player->setPlace($room);
        $room->setDaedalus($player->getDaedalus());

        $action
            ->setActionName(ActionEnum::DROP)
        ;

        $this->eventService->shouldReceive('callEvent')->twice();

        $player = $this->actionService->handleActionSideEffect($action, $player, null);

        $this->assertCount(0, $player->getStatuses());
    }

    public function testHandleActionSideEffectAlreadyDirty()
    {
        $action = new Action();
        $room = new Place();
        $player = new Player();
        $player->setDaedalus(new Daedalus());
        $player->setPlace($room);
        $room->setDaedalus($player->getDaedalus());

        $action
            ->setActionName(ActionEnum::DROP)
        ;
        $dirtyConfig = new StatusConfig();
        $dirtyConfig->setStatusName(PlayerStatusEnum::DIRTY);
        new Status($player, $dirtyConfig);

        $this->eventService->shouldReceive('callEvent')->once();

        $player = $this->actionService->handleActionSideEffect($action, $player, null);

        $this->assertCount(1, $player->getStatuses());
    }
}
