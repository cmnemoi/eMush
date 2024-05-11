<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Hide;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class HideActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::HIDE, 1);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new Hide(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());

        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($player);

        $item = new ItemConfig();
        $item
            ->setActionConfigs(new ArrayCollection([$this->actionConfig]));
        $gameItem
            ->setName('itemName')
            ->setEquipment($item);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                static fn (AbstractGameEvent $event) => $event instanceof InteractWithEquipmentEvent
                && $event->getGameEquipment() === $gameItem
                && $event->getAuthor() === $player
                && $event->getTags() === [ActionEnum::HIDE->value]
            )
            ->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
