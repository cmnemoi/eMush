<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\EventEnum;
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
        $equipmentBrokenByCycleChange = $event->getReason() === EventEnum::NEW_CYCLE && $holder instanceof GameEquipment;
        //@TODO : $brokenByGreenJelly = $event->getReason() === EventEnum::GREEN_JELLY;

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                //@TODO : if ($brokenByGreenJelly || $equipmentBrokenByCycleChange)
                if ($equipmentBrokenByCycleChange) {
                    $this->neronMessageService->createBrokenEquipmentMessage($holder, $event->getVisibility(), $event->getTime());
                }

                return;

            case StatusEnum::FIRE:
                $daedalus = $event->getPlace()->getDaedalus();
                $this->neronMessageService->createNewFireMessage($daedalus, $event->getTime());

                return;
        }
    }
}
