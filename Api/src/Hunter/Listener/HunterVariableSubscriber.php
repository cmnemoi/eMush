<?php

namespace Mush\Hunter\Listener;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterVariableSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private HunterServiceInterface $hunterService;

    public function __construct(
        EventServiceInterface $eventService,
        HunterServiceInterface $hunterService,
    ) {
        $this->eventService = $eventService;
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

        $this->changeVariable($event);
    }

    private function changeVariable(HunterVariableEvent $event): void
    {
        $author = $event->getAuthor();
        $change = $event->getQuantity();
        $date = $event->getTime();
        $hunter = $event->getHunter();
        $variableName = $event->getVariableName();

        $gameVariable = $hunter->getVariableByName($variableName);
        $newVariableValuePoint = $gameVariable->getValue() + $change;

        $hunter->setVariableValueByName($newVariableValuePoint, $variableName);

        switch ($variableName) {
            case HunterVariableEnum::HEALTH:
                if ($newVariableValuePoint <= 0) {
                    $hunterDeathEvent = new HunterEvent(
                        $hunter,
                        VisibilityEnum::PUBLIC,
                        array_merge($event->getTags(), [HunterEvent::HUNTER_DEATH]),
                        $date
                    );
                    $hunterDeathEvent->setAuthor($author);
                    $this->eventService->callEvent($hunterDeathEvent, HunterEvent::HUNTER_DEATH);
                }

                return;
            default:
                return;
        }

        $this->hunterService->persist([$hunter]);
    }
}
