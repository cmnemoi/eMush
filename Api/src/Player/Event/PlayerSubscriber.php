<?php


namespace Mush\Player\Event;


use Mush\Game\Event\CycleEvent;
use Mush\Game\Event\DayEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            CycleEvent::NEW_CYCLE => 'onNewCycle',
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewPlayer(PlayerEvent $event)
    {
        $player = $event->getPlayer();
        // @TODO: create logs
    }

    public function onDeathPlayer(PlayerEvent $event)
    {
        $player = $event->getPlayer();
        // @TODO: create logs
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($player = $event->getPlayer())) {
            return;
        }

        $player
            ->setActionPoint($player->getActionPoint() + 1)
        ;

        $this->playerService->persist($player);
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($player = $event->getPlayer())) {
            return;
        }

        $player
            ->setHealthPoint($player->getHealthPoint() + 1)
        ;

        $this->playerService->persist($player);
    }
}