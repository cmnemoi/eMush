<?php

namespace Mush\Communication\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
        TranslationServiceInterface $translationService
    ) {
        $this->neronMessageService = $neronMessageService;
        $this->translationService = $translationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
            DaedalusEvent::TRAVEL_FINISHED => 'onTravelFinished',
            DaedalusEvent::CHANGED_ORIENTATION => 'onChangedOrientation',
        ];
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->neronMessageService->createNeronMessage(NeronMessageEnum::START_GAME, $daedalus, [], $event->getTime());
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        if ($daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT)) {
            $this->neronMessageService->createNeronMessage(NeronMessageEnum::TRAVEL_PLANET, $daedalus, [], $event->getTime());
        }

        if (\in_array(ActionEnum::LEAVE_ORBIT->value, $event->getTags(), true)) {
            $this->neronMessageService->createNeronMessage(NeronMessageEnum::LEAVE_ORBIT, $daedalus, [], $event->getTime());
        } else {
            $this->neronMessageService->createNeronMessage(NeronMessageEnum::TRAVEL_DEFAULT, $daedalus, [], $event->getTime());
        }
    }

    public function onTravelFinished(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->neronMessageService->createNeronMessage(NeronMessageEnum::TRAVEL_ARRIVAL, $daedalus, [], $event->getTime());
    }

    public function onChangedOrientation(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $translatedOrientation = $this->translationService->translate(
            key: $daedalus->getOrientation(),
            parameters: [],
            domain: 'misc',
            language: $daedalus->getDaedalus()->getLanguage()
        );

        $this->neronMessageService->createNeronMessage(
            NeronMessageEnum::CHANGE_HEADING,
            $daedalus,
            ['direction' => $translatedOrientation],
            $event->getTime()
        );
    }
}
