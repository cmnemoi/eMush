<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;

class Fire extends AbstractStatusCycleHandler
{
    protected string $name = StatusEnum::FIRE;

    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService
    ) {
        $this->randomService = $randomService;
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }

    public function handleNewCycle(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime, array $context = []): void
    {
        if (!$status instanceof ChargeStatus || $status->getName() !== StatusEnum::FIRE) {
            return;
        }

        if (!$statusHolder instanceof Place) {
            throw new \LogicException('Fire status does not have a room');
        }

        // If fire is active
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

            if (!$adjacentRoom->hasStatus(StatusEnum::FIRE)
                && $this->randomService->isSuccessful($difficultyConfig->getPropagatingFireRate())
            ) {
                $statusEvent = new StatusEvent(
                    StatusEnum::FIRE,
                    $adjacentRoom,
                    [RoomEventEnum::PROPAGATING_FIRE],
                    $date
                );
                $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
            }
        }

        return $room;
    }

    private function fireDamage(Place $room, \DateTime $date): Place
    {
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();

        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($difficultyConfig->getFirePlayerDamage());

            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                [StatusEnum::FIRE],
                $date
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        foreach ($room->getEquipments() as $equipment) {
            $this->gameEquipmentService->handleBreakFire($equipment, $date);
        }

        if ($this->randomService->isSuccessful($difficultyConfig->getHullFireDamageRate())) {
            $damage = intval($this->randomService->getSingleRandomElementFromProbaCollection($difficultyConfig->getFireHullDamage()));

            $daedalusEvent = new DaedalusVariableEvent(
                $room->getDaedalus(),
                DaedalusVariableEnum::HULL,
                -$damage,
                [RoomEventEnum::CYCLE_FIRE],
                $date
            );

            $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);

            $this->daedalusService->persist($room->getDaedalus());
        }

        return $room;
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
