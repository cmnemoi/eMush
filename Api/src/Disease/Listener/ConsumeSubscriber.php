<?php

namespace Mush\Disease\Listener;

use Mush\Action\Event\ConsumeEvent;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsumeSubscriber implements EventSubscriberInterface
{
    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(DiseaseCauseServiceInterface $diseaseCauseService)
    {
        $this->diseaseCauseService = $diseaseCauseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsumeEvent::CONSUME => 'onConsume',
        ];
    }

    public function onConsume(ConsumeEvent $event)
    {
        $equipment = $event->getGameItem();

        $this->diseaseCauseService->handleSpoiledFood($event->getPlayer(), $equipment);
    }
}
