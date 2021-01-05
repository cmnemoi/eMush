<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
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
    private GameConfig $gameConfig;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::NEW_DAEDALUS => 'onDaedalusNew',
            DaedalusEvent::END_DAEDALUS => 'onDaedalusEnd',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
        ];
    }

    public function onDaedalusNew(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
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

        $daedalus->setFinishedAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::FINISHED);
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        //@TODO: create logs

        //@TODO give titles

        //Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus);

        $daedalus->setFilledAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
    }
}
