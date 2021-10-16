<?php

namespace Mush\Communication\Listener;

use Mush\Action\Event\ApplyEffectEventInterface;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
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
            ApplyEffectEventInterface::REPORT_FIRE => 'onReportFire',
            ApplyEffectEventInterface::REPORT_EQUIPMENT => 'onReportEquipment',
        ];
    }

    public function onReportFire(ApplyEffectEventInterface $event): void
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

    public function onReportEquipment(ApplyEffectEventInterface $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();
        $equipment = $event->getParameter();

        if (!$equipment instanceof GameEquipment) {
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
