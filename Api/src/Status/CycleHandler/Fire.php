<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
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
    ) {}

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

        // Make sure the fire will be set only on Rooms.
        if ($statusHolder->getType() !== PlaceTypeEnum::ROOM) {
            return;
        }

        // if Auto Watering project is finished and random draw is successful, the fire is extinguished.
        if ($this->isFireKilledByProject($statusHolder, $dateTime)) {
            return;
        }

        $this->fireDamage($statusHolder, $dateTime);
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void {}

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

    private function isFireKilledByProject(StatusHolderInterface $statusHolder, \DateTime $dateTime): bool
    {
        $daedalus = $statusHolder->getDaedalus();
        $autoWatering = $daedalus->getProjectByName(ProjectName::AUTO_WATERING);
        if ($autoWatering->isFinished() && $this->randomService->isSuccessful($autoWatering->getActivationRate())) {
            $this->statusService->removeStatus(
                statusName: $this->name,
                holder: $statusHolder,
                tags: [StatusEnum::FIRE],
                time: $dateTime
            );
            $this->statusService->createOrIncrementChargeStatus(
                name: DaedalusStatusEnum::AUTO_WATERING_KILLED_FIRES,
                holder: $daedalus,
                time: $dateTime
            );

            return true;
        }

        return false;
    }
}
