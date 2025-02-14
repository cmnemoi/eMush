<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Service\KillLinkWithSolService;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private KillLinkWithSolService $killLinkWithSol) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        if ($this->commsCenterGotBroken($event)) {
            $this->killLinkWithSol->execute($event->getDaedalus()->getId());
        }
    }

    public function commsCenterGotBroken(StatusEvent $event): bool
    {
        return $event->getStatusHolder()->getName() === EquipmentEnum::COMMUNICATION_CENTER
            && $event->getStatusName() === EquipmentStatusEnum::BROKEN;
    }
}
