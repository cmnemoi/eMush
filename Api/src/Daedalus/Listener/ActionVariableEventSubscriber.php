<?php

declare(strict_types=1);

namespace Mush\Daedalus\Listener;

use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ActionVariableEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private DaedalusRepositoryInterface $daedalusRepository) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
        ];
    }

    public function onApplyCost(ActionVariableEvent $event): void
    {
        if ($event->getVariableName() !== PlayerVariableEnum::ACTION_POINT) {
            return;
        }

        $daedalus = $event->getDaedalus();
        $daedalus->addDailyActionPointsSpent($event->getRoundedQuantity());
        $this->daedalusRepository->save($daedalus);
    }
}