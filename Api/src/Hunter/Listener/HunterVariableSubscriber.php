<?php

namespace Mush\Hunter\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Enum\HunterVariableEnum;
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

        $this->changeVariable($event);
    }

    private function changeVariable(HunterVariableEvent $event): void
    {
        $author = $event->getAuthor();
        $change = $event->getRoundedQuantity();
        $date = $event->getTime();
        $hunter = $event->getHunter();
        $variableName = $event->getVariableName();

        $gameVariable = $hunter->getVariableByName($variableName);
        $newVariableValuePoint = $gameVariable->getValue() + $change;

        $hunter->setVariableValueByName($newVariableValuePoint, $variableName);

        switch ($variableName) {
            case HunterVariableEnum::HEALTH:
                if ($newVariableValuePoint <= 0) {
                    $this->hunterService->killHunter($hunter, $event->getTags(), $author);
                }

                return;
            default:
                return;
        }

        $this->hunterService->persist([$hunter]);
    }
}
