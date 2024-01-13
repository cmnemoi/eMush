<?php

namespace Mush\Communication\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\LanguageEnum;
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
        $player = $event->getAuthor();
        $daedalus = $player->getDaedalus();
        $place = $event->getPlace();

        $parentMessage = $this->neronMessageService->getMessageNeronCycleFailures($daedalus, $event->getTime(), $event->getTags());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_FIRE,
            $player->getDaedalus(),
            [LanguageEnum::CHARACTER => $player->getLogName(), LanguageEnum::ROOMS => $place->getName()],
            new \DateTime(),
            $parentMessage
        );
    }

    public function onReportEquipment(ApplyEffectEvent $event): void
    {
        $player = $event->getAuthor();
        $daedalus = $player->getDaedalus();
        $equipment = $event->getParameter();

        if (!$equipment instanceof GameEquipment) {
            throw new \LogicException('equipment should not be null');
        }

        $parentMessage = $this->neronMessageService->getMessageNeronCycleFailures($daedalus, $event->getTime(), $event->getTags());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_EQUIPMENT,
            $player->getDaedalus(),
            ['character' => $player->getLogName(), $equipment->getLogKey() => $equipment->getLogName()],
            new \DateTime(),
            $parentMessage
        );
    }
}
