<?php

namespace Mush\Item\Event;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Event\CycleEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;
    private ContainerInterface $container;
    private array $cyclesManagerConfig;

    public function __construct(
        array $cyclesManagerConfig,
        RoomServiceInterface $roomService,
        EventDispatcherInterface $eventDispatcher,
        ContainerInterface $container
    ) {
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
        $this->cyclesManagerConfig = $cyclesManagerConfig;
    }

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($item = $event->getGameItem())) {
            return;
        }

        foreach ($item->getStatuses() as $status) {
            $statusNewCycle = new CycleEvent($event->getDaedalus(), $event->getTime());
            $statusNewCycle->setStatus($status);
            $this->eventDispatcher->dispatch($statusNewCycle, CycleEvent::NEW_CYCLE);
        }

        foreach ($item->getItem()->getTypes() as $itemType) {
            if (isset($this->cyclesManagerConfig[get_class($itemType)])) {
                $serviceClass = $this->cyclesManagerConfig[get_class($itemType)];
                /** @var CycleHandlerInterface $service */
                $service = $this->container->get($serviceClass);
                $service->handleNewCycle($item, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
