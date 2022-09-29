<?php

namespace Mush\Communication\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\Equipment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplyEffectSubscriber implements EventSubscriberInterface
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
            ApplyEffectEvent::REPORT_FIRE => 'onReportFire',
            ApplyEffectEvent::REPORT_EQUIPMENT => 'onReportEquipment',
        ];
    }

    public function onReportFire(ApplyEffectEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();
        $place = $event->getPlace();

        $parentMessage = $this->neronMessageService->getMessageNeronCycleFailures($daedalus, new \DateTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_FIRE,
            $player->getDaedalus(),
            ['character' => $player->getLogName(), 'place' => $place->getName()],
            new \DateTime(),
            $parentMessage
        );
    }

    public function onReportEquipment(ApplyEffectEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();
        $equipment = $event->getParameter();

        if (!$equipment instanceof Equipment) {
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
