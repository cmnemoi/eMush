<?php

namespace Mush\Communication\Listener;

use Mush\Action\Event\ReportEvent;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService
    ) {
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReportEvent::REPORT_FIRE => 'onReportFire',
            ReportEvent::REPORT_EQUIPMENT => 'onReportEquipment',
        ];
    }

    public function onReportFire(ReportEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();
        $place = $event->getPlace();

        if ($place === null) {
            throw new \LogicException('place should not be null');
        }

        $parentMessage = $this->neronMessageService->getMessageNeronCycleFailures($daedalus, new \DateTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_FIRE,
            $player->getDaedalus(),
            ['character' => $player->getLogName(), 'place' => $place->getName()],
            new \DateTime(),
            $parentMessage
        );
    }

    public function onReportEquipment(ReportEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();
        $equipment = $event->getGameEquipment();

        if ($equipment === null) {
            throw new \LogicException('equipment should not be null');
        }

        $parentMessage = $this->neronMessageService->getMessageNeronCycleFailures($daedalus, new \DateTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_EQUIPMENT,
            $player->getDaedalus(),
            ['character' => $player->getLogName(), $equipment->getLogKey() => $equipment->getLogName()],
            new \DateTime(),
            $parentMessage
        );
    }
}
