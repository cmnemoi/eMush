<?php

namespace Mush\Player\Event;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;

    /**
     * DaedalusSubscriber constructor.
     */
    public function __construct(
        DaedalusServiceInterface $daedalusService,
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
        ) {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::NEW_DAEDALUS => 'onDaedalusNew',
            DaedalusEvent::END_DAEDALUS => 'onDaedalusEnd',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
        ];
    }

    public function onDaedalusNew(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
    }

    public function onDaedalusEnd(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
        // @TODO: create logs
    }

    public function onDaedalusFull(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
        // @TODO: create logs

        //@TODO give titles

        //Chose alpha Mushs
        $chancesArray = [];
        foreach ($daedalus->getPlayers() as $player) {
            //@TODO lower $mushChance if user is a beginner
            //@TODO (maybe add a "I want to be mush" setting to increase this proba)
            $mushChance = 1;

            if ($player->getPerson() !== CharacterEnum::CHUN) {
                $mushChance = 0;
            }
            $chancesArray[$player->getPerson()] = $mushChance;
        }

        //@TODO better handle the initial number of mush (related to private games)
        $mushNumberSetting = 2;
        $mushNumber = round($daedalus->getPlayers()->count() / 16 * $mushNumberSetting);

        $mushPlayerName = $this->randomService->getRandomElementsFromProbaArray($chancesArray, $mushNumber);

        foreach ($mushPlayerName as $playerName) {
            $player = $daedalus->getPlayers()->filter(fn (Player $player) => $player->getPerson() === $playerName)->first();
            $this->statusService->createMushStatus($player);
        }
    }
}
