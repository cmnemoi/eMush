<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Enum\TradeEnum;
use Mush\Communications\Service\KillLinkWithSolService;
use Mush\Communications\Service\PrintDocumentServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateHunterService $createHunterService,
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

        if ($this->playerBecameHighlyInactive($event)) {
            $this->createHunterService->execute(
                hunterName: HunterEnum::TRANSPORT,
                daedalusId: $event->getDaedalus()->getId(),
                time: $event->getTime(),
                forcedTradeTypes: TradeEnum::getHumanTrades(),
            );
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

    private function playerBecameHighlyInactive(StatusEvent $event): bool
    {
        return $event->getStatusName() === PlayerStatusEnum::HIGHLY_INACTIVE;
    }

    private function isTabulatrixFixed(StatusEvent $event): bool
    {
        return $event->getStatusHolder()->getName() === EquipmentEnum::TABULATRIX
            && $event->getStatusName() === EquipmentStatusEnum::BROKEN;
    }
}
