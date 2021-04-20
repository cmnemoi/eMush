<?php

namespace Mush\Daedalus\Event;

use Mush\Communication\Services\MessageServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private MessageServiceInterface $messageService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        MessageServiceInterface $messageService,
    ) {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::END_DAEDALUS => 'onDaedalusEnd',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
        ];
    }

    public function onDaedalusEnd(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $reason = $event->getReason();

        if (!$reason) {
            throw new \LogicException('daedalus should end with a reason');
        }

        $this->daedalusService->killRemainingPlayers($daedalus, $reason);

        // @TODO: create logs
        // @TODO: remove all fire and charged statuses

        $daedalus->setFinishedAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::FINISHED);
        $this->daedalusService->persist($daedalus);
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        //@TODO give titles

        //Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus);

        $daedalus->setFilledAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
        $this->daedalusService->persist($daedalus);
    }
}
