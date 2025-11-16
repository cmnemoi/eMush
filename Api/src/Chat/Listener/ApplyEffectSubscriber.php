<?php

namespace Mush\Chat\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
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

        // last update of a message is disabled by default (to avoid players to up messages every time they read it)
        // we need to update it manually so failure thread gets uped in the tchat
        $parentMessage->setUpdatedAt($event->getTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_FIRE,
            $player->getDaedalus(),
            [LanguageEnum::CHARACTER => $player->getLogName(), LanguageEnum::ROOMS => $place->getName()],
            $event->getTime(),
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

        // last update of a message is disabled by default (to avoid players to up messages every time they read it)
        // we need to update it manually so failure thread gets uped in the tchat
        $parentMessage->setUpdatedAt($event->getTime());

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::REPORT_EQUIPMENT,
            $player->getDaedalus(),
            ['character' => $player->getLogName(), $equipment->getLogKey() => $equipment->getLogName()],
            $event->getTime(),
            $parentMessage
        );
    }
}
