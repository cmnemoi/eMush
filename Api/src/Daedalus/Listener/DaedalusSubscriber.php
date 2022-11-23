<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService
    ) {
        $this->daedalusService = $daedalusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::FINISH_DAEDALUS => 'onDaedalusFinish',
            DaedalusEvent::CLOSE_DAEDALUS => 'onCloseDaedalus',
        ];
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $reason = $event->getReason();

        if (!$reason) {
            throw new \LogicException('daedalus should end with a reason');
        }

        $this->daedalusService->endDaedalus($daedalus, $reason, $event->getTime());
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        // @TODO give titles

        // Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus, $event->getTime());

        $daedalus->setFilledAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
        $this->daedalusService->persist($daedalus);
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->daedalusService->startDaedalus($daedalus);
    }
}
