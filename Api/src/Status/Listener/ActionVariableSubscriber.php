<?php

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionVariableSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;
    private RandomServiceInterface $randomService;

    public function __construct(
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService
    ) {
        $this->statusService = $statusService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::ROLL_ACTION_PERCENTAGE => 'onRollPercentage',
        ];
    }

    public function onRollPercentage(ActionVariableEvent $event): void
    {
        if ($event->getVariableName() === ActionVariableEnum::PERCENTAGE_DIRTINESS) {
            $isDirty = $this->randomService->isSuccessful($event->getRoundedQuantity());
            $tags = $event->getTags();

            if ($isDirty) {
                $this->statusService->createStatusFromName(
                    PlayerStatusEnum::DIRTY,
                    $event->getAuthor(),
                    $tags,
                    $event->getTime(),
                    null,
                    VisibilityEnum::PRIVATE
                );
            }
        }
    }
}
