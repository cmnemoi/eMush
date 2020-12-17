<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    /**
     * DaedalusSubscriber constructor.
     */
    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
        ) {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::NEW_DAEDALUS => 'onDaedalusNew',
            DaedalusEvent::END_DAEDALUS => 'onDaedalusEnd',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
        ];
    }

    public function onDaedalusNew(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
    }

    public function onDaedalusEnd(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
        // @TODO: create logs
    }

    public function onDaedalusFull(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
        // @TODO: create logs

        //@TODO give titles

        //Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus);
    }
}
