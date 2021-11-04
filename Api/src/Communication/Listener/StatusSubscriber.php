<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
    ) {
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                $this->neronMessageService->createBrokenEquipmentMessage($holder, $event->getVisibility(), $event->getTime());

                return;

            case StatusEnum::FIRE:
                $daedalus = $event->getPlace()->getDaedalus();
                $this->neronMessageService->createNewFireMessage($daedalus, $event->getTime());

                return;
        }
    }
}
