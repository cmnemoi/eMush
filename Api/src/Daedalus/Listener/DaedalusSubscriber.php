<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->daedalusService = $daedalusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::FINISH_DAEDALUS => 'onDaedalusFinish',
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', EventPriorityEnum::LOW],
        ];
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

        if (!$endCause) {
            throw new \LogicException('daedalus should end with a reason');
        }

        $this->daedalusService->endDaedalus($daedalus, $endCause, $event->getTime());
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusInfo = $daedalus->getDaedalusInfo();

        // Assign Titles
        $this->daedalusService->attributeTitles($daedalus, $event->getTime());

        // Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus, $event->getTime());

        $daedalus->setFilledAt(new \DateTime());
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $this->daedalusService->persist($daedalus);

        // handle mush random spore if option is active
        if ($daedalus->getGameConfig()->hasSpecialOption(GameConfigEnum::OPTION_RANDOM_SPORE)) {
            $this->handleRandomSpores($daedalus, $event);
        }
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->daedalusService->startDaedalus($daedalus);
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalus->setCombustionChamberFuel(0);

        $this->daedalusService->persist($daedalus);
    }

    private function handleRandomSpores(Daedalus $daedalus, DaedalusEvent $event): void
    {
        $humanPlayers = $daedalus->getPlayers()->getActiveNonImmuneHumanPlayers();
        $sporeToGive = $this->randomService->getSingleRandomElementFromProbaCollection($daedalus->getGameConfig()->getDifficultyConfig()->getRandomSpores());

        if (!\is_int($sporeToGive)) {
            return;
        }

        /** @var Player $human */
        foreach ($this->randomService->getRandomElements($humanPlayers->toArray(), $sporeToGive) as $human) {
            $playerModifierEvent = new PlayerVariableEvent(
                $human,
                PlayerVariableEnum::SPORE,
                1,
                $event->getTags(),
                new \DateTime(),
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
