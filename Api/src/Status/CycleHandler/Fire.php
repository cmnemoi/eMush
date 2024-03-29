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
use Mush\Status\Service\StatusServiceInterface;

final readonly class Fire extends AbstractStatusCycleHandler
{
    public function __construct(
        private RandomServiceInterface $randomService,
        private EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private DaedalusServiceInterface $daedalusService,
        private StatusServiceInterface $statusService,

        protected string $name = StatusEnum::FIRE,
    ) {
    }

    /**
     * @param Status                $status       apparently the status of the fire
     * @param StatusHolderInterface $statusHolder The place of the fire
     * @param \DateTime             $dateTime     Date time of the event propagated. Dispatched for the event generation.
     * @param array                 $context      unused context
     */
    public function handleNewCycle(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime, array $context = []): void
    {
        if (!$status instanceof ChargeStatus || $status->getName() !== $this->name) {
            return;
        }

        if (!$statusHolder instanceof Place) {
            throw new \LogicException('Fire status does not have a room');
        }

        if ($status->getCharge() === 0) {
            return;
        }

        // The fire is then active. Damage existing room then propagate new fires.
        $this->fireDamage($statusHolder, $dateTime);
        $this->propagateFire($statusHolder, $dateTime);
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }

    private function propagateFire(Place $room, \DateTime $date): void
    {
        $allFires = $room->getDaedalus()->getRooms()->filter(fn (Place $place) => $place->hasStatus($this->name));
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        // Get the lowest number between actual fires or the rate allowed by the difficulty.
        $maxPropagation = min($difficultyConfig->getMaximumAllowedSpreadingFires(), $allFires->count());

        /** @var Place $roomToPropagate */
        foreach ($this->randomService->getRandomElements($allFires->toArray(), $maxPropagation) as $roomToPropagate) {
            if (!$this->randomService->isSuccessful($difficultyConfig->getPropagatingFireRate())) {
                // Next room.
                continue;
            }

            // The random service has taken the lead, the fire will propagate but where? So:
            // Get all adjacent rooms that are not on fire. Then:
            $adjacentCleanRooms = $roomToPropagate
                ->getDoors()
                ->filter(fn (Door $door) => !$door->getOtherRoom($roomToPropagate)->hasStatus($this->name))
                ->map(fn (Door $door) => $door->getOtherRoom($roomToPropagate))->toArray();

            if (empty($adjacentCleanRooms)) {
                continue;
            }

            // Bring fire and destruction.
            $this->statusService->createStatusFromName(
                $this->name,
                $this->randomService->getRandomElement($adjacentCleanRooms),
                [RoomEventEnum::PROPAGATING_FIRE],
                $date
            );
        }
    }

    private function fireDamage(Place $room, \DateTime $date): void
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
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($difficultyConfig->getFireHullDamage());

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
    }
}
