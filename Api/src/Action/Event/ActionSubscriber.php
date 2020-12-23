<?php

namespace Mush\Action\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $player = $event->getPlayer();
        $actionCost = $event->getActionCost();

        if ($lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            $lyingDownStatus->setPlayer(null)->setGameEquipment(null);
            $this->statusService->delete($lyingDownStatus);

            //@TODO increase cost of action
            $this->roomLogService->createPlayerLog(
                ActionEnum::GET_UP,
                $player->getRoom(),
                $player,
                VisibilityEnum::PUBLIC,
                new \DateTime('now')
            );
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $daedalus = $event->getAction();
        // @TODO: create logs
    }
}
