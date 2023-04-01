<?php

namespace Mush\Hunter\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterVariableSubscriber implements EventSubscriberInterface
{
    private HunterServiceInterface $hunterService;

    public function __construct(
        HunterServiceInterface $hunterService,
    ) {
        $this->hunterService = $hunterService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof HunterVariableEvent) {
            return;
        }

        $hunter = $event->getHunter();
        $date = $event->getTime();
        $change = $event->getQuantity();
        $author = $event->getAuthor();
        if (!$author) {
            throw new \Exception('HunterVariableEvent should have an author');
        }

        $this->hunterService->changeVariable($event->getVariableName(), $hunter, $change, $date, $author);
    }
}
