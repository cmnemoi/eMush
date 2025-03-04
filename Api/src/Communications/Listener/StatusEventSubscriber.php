<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Service\KillLinkWithSolService;
use Mush\Communications\Service\PrintDocumentServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private KillLinkWithSolService $killLinkWithSol,
        private PrintDocumentServiceInterface $printDocumentService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_DELETED => 'onStatusDeleted',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        if ($this->commsCenterGotBroken($event)) {
            $this->killLinkWithSol->execute($event->getDaedalus()->getId());
        }
    }

    public function onStatusDeleted(StatusEvent $event): void
    {
        if ($this->isTabulatrixFixed($event)) {
            $tabulatrix = $event->getGameEquipmentStatusHolder();

            $this->printDocumentService->execute(
                printer: $tabulatrix,
                tags: $event->getTags(),
            );
        }
    }

    private function commsCenterGotBroken(StatusEvent $event): bool
    {
        return $event->getStatusHolder()->getName() === EquipmentEnum::COMMUNICATION_CENTER
            && $event->getStatusName() === EquipmentStatusEnum::BROKEN;
    }

    private function isTabulatrixFixed(StatusEvent $event): bool
    {
        return $event->getStatusHolder()->getName() === EquipmentEnum::TABULATRIX
            && $event->getStatusName() === EquipmentStatusEnum::BROKEN;
    }
}
