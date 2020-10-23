<?php

namespace Mush\Item\Event;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Event\DayEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
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
        $this->cyclesManagerConfig = $cyclesManagerConfig;
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($item = $event->getItem())) {
            return;
        }

        if (isset($this->cyclesManagerConfig[get_class($item)])) {
            $serviceClass = $this->cyclesManagerConfig[get_class($item)];
            /** @var CycleHandlerInterface $service */
            $service = $this->container->get($serviceClass);
            $service->handleNewDay($item, $event->getTime());
        }
    }
}
