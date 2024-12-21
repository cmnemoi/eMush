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

        $this->changeHunterVariableFrom($event);

        $hunter = $event->getHunter();
        if ($hunter->hasNoHealth()) {
            $this->hunterService->killHunter($hunter, $event->getTags(), $event->getAuthor());
        }
    }

    private function changeHunterVariableFrom(HunterVariableEvent $event): void
    {
        $hunter = $event->getHunter();
        $variableName = $event->getVariableName();

        $hunter->changeVariableValueByName($event->getRoundedQuantity(), $variableName);
        $this->hunterService->persist([$hunter]);
    }
}
