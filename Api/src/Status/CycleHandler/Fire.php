<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Fire extends AbstractStatusCycleHandler
{
    protected string $name = StatusEnum::FIRE;

    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService
    ) {
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }

    public function handleNewCycle(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime, array $context = []): void
    {
        if (!$status instanceof ChargeStatus || $status->getName() !== StatusEnum::FIRE) {
            return;
        }

        if (!$statusHolder instanceof Place) {
            throw new \LogicException('Fire status does not have a room');
        }

        //If fire is active
        if ($status->getCharge() > 0) {
            $this->propagateFire($statusHolder, $dateTime);
            $this->fireDamage($statusHolder, $dateTime);
        }
    }

    private function propagateFire(Place $room, \DateTime $date): Place
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        /** @var Door $door */
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if (!$adjacentRoom->hasStatus(StatusEnum::FIRE) &&
                $this->randomService->isSuccessful($difficultyConfig->getPropagatingFireRate())
            ) {
                $roomEvent = new RoomEvent($adjacentRoom, $date);
                $roomEvent->setReason(RoomEventEnum::PROPAGATING_FIRE);
                $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
            }
        }

        return $room;
    }

    private function fireDamage(Place $room, \DateTime $date): Place
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getFirePlayerDamage());

            $playerModifierEvent = new PlayerModifierEvent($player, -$damage, $date);
            $playerModifierEvent->setReason(EndCauseEnum::BURNT);
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);
        }

        foreach ($room->getEquipments() as $equipment) {
            $this->gameEquipmentService->handleBreakFire($equipment, $date);
        }

        if ($this->randomService->isSuccessful($difficultyConfig->getHullFireDamageRate())) {
            $damage = intval($this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getFireHullDamage()));

            $daedalusEvent = new DaedalusEvent($room->getDaedalus(), $date);
            $daedalusEvent->setQuantity(-$damage);

            $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::CHANGE_HULL);

            $this->daedalusService->persist($room->getDaedalus());
        }

        return $room;
    }

    public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
        return;
    }
}
