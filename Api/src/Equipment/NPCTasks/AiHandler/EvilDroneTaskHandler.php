<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\AIHandlerEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\DroneMovedEvent;
use Mush\Equipment\NPCTasks\EvilDrone\EvilDroneTasks;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationService;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * Basic explantions
 * The drone try to select a target if he doesn't have one.
 * If it roll a success on its idle chance then it automatically fail to get a target
 * If a player is dead, then it select them as a target in priority regardless of distance
 * Else it select a player or NERON Core
 * It keep in memory players and neron to know to avoid repeating taks. (Conspire can be repeated with a delay)
 * if neron or player are too far, it can't select them as target
 * If it has no target after trying to get one, it does it's idle task
 * If it has a target it try to move toward it until it doesn't have charges
 * If it end its turn in the same room as its target, it execute the related task.
 * If not on the daedalus, it ask loudly to be released.
 */
class EvilDroneTaskHandler extends AbstractAiHandler
{
    protected string $name = AIHandlerEnum::DRONE_EVIL->value;
    private int $idleChance = 66;

    public function __construct(
        private StatusServiceInterface $statusService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
        private RoomLogServiceInterface $roomLogService,
        private FindNextRoomTowardsConditionService $findNextRoomTowardsConditionService,
        private EventServiceInterface $eventService,
        private EvilDroneTasks $evilDroneTasks,
        private TranslationService $translationService,
    ) {}

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        if (!$NPC instanceof Drone) {
            throw new \RuntimeException('Evil Drone should be a drone');
        }

        if ($this->handleKidnapping($NPC, $time)) {
            return;
        }

        $status = $NPC->getStatusByName(EquipmentStatusEnum::EVIL_DRONE_TARGET);

        if ($status === null
            || RoomEnum::getChebyshevDistance($NPC->getPlace()->getName(), $status->getTargetOrThrow()->getPlace()->getName()) > 30
        ) {
            $status = $this->getNewTarget($NPC, $time);
        }

        if ($status === null) {
            $this->moveDroneRandomly($NPC, $time);
            $this->evilDroneTasks->executeIdleTask($NPC, $time);
        } else {
            $this->handleMoveTowardTarget($NPC, $status->getTargetOrThrow(), $time);

            if ($this->hasReachedDestination($NPC, $status->getTargetOrThrow()->getPlace())) {
                $this->evilDroneTasks->executeTask($NPC, $status->getTargetOrThrow(), $time);
            }
        }
    }

    public function setDoNothing(int $chance): self
    {
        $this->idleChance = $chance;

        return $this;
    }

    private function findRandomAdjacentRoom(Drone $drone): ?Place
    {
        return $this->randomService->getRandomElement(
            $drone->getPlace()->getAccessibleRooms()->toArray()
        );
    }

    private function moveDroneRandomly(Drone $NPC, \DateTime $time)
    {
        $oldPlace = $NPC->getPlace();
        $nextPlace = $this->findRandomAdjacentRoom($NPC);
        if ($nextPlace === null) {
            return;
        }

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $NPC,
            newHolder: $nextPlace,
            time: $time
        );

        $this->dispatchDroneMovedEvent($NPC, $oldPlace, $time);
    }

    private function handleKidnapping(Drone $NPC, \DateTime $time): bool
    {
        if ($NPC->getPlace()->isNotARoom()) {
            $this->roomLogService->createLog(
                logKey: 'evil_drone.kidnapping',
                place: $NPC->getPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                parameters: ['drone' => $this->getLogName($NPC)],
                dateTime: $time,
            );

            return true;
        }

        return false;
    }

    private function getLogName(Drone $npc): string
    {
        return $this->translationService->translate(
            key: 'drone',
            parameters: ['drone_nickname' => $npc->getNickname(), 'drone_serial_number' => $npc->getSerialNumber()],
            domain: 'event_log',
            language: $npc->getDaedalus()->getLanguage()
        );
    }

    private function handleMoveTowardTarget(Drone $NPC, StatusHolderInterface $target, \DateTime $time)
    {
        $destination = $target->getPlace();
        $charges = $this->getMoveCharges($NPC);

        while ($charges->getCharge() > 0 && $this->hasReachedDestination($NPC, $destination) === false) {
            $this->moveDrone($NPC, $destination, $time);
        }
    }

    private function hasReachedDestination(Drone $NPC, Place $destination): bool
    {
        return $NPC->getPlace()->getId() === $destination->getId();
    }

    private function getMoveCharges(Drone $NPC): ChargeStatus
    {
        return $NPC->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
    }

    private function moveDrone(Drone $NPC, Place $destination, \DateTime $time)
    {
        $oldPlace = $NPC->getPlace();
        $nextPlace = $this->findNextRoomTowardsConditionService->execute($oldPlace, static fn (Place $place) => $place->getId() === $destination->getId());

        if ($nextPlace === null) {
            return;
        }

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $NPC,
            newHolder: $nextPlace,
            time: $time
        );

        $this->dispatchDroneMovedEvent($NPC, $oldPlace, $time);

        $this->statusService->updateCharge(
            $NPC->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
            -1,
            [],
            $time
        );
    }

    private function dispatchDroneMovedEvent(Drone $drone, Place $oldRoom, \DateTime $time): void
    {
        $droneEvent = new DroneMovedEvent(
            drone: $drone,
            oldRoom: $oldRoom,
            time: $time
        );
        $this->eventService->callEvent($droneEvent, DroneMovedEvent::class);
    }

    private function getNeron(Drone $NPC, ArrayCollection $arrayCollection): void
    {
        $neron = $this->gameEquipmentService->findEquipmentsByNameAndDaedalus(EquipmentEnum::NERON_CORE, $NPC->getDaedalus())->first();

        if ($neron !== false && self::targetNotTooFar($NPC, $neron) && self::targetNotVisitedLess4Cycles($NPC, $neron)) {
            $arrayCollection->add($neron);
        }
    }

    private function getDeadPlayer(Drone $NPC): ?Player
    {
        $deadPlayers = $NPC->getDaedalus()->getPlayers()->getPlayerDead();
        $deadPlayers = $deadPlayers->filter(static fn (Player $player) => self::playerCanBeRecycled($NPC, $player));

        if ($deadPlayers->isEmpty() === false) {
            return $this->randomService->getRandomElement($deadPlayers->toArray());
        }

        return null;
    }

    private function getLivingPlayer(Drone $NPC, ArrayCollection $arrayCollection): void
    {
        $alivePlayers = $NPC->getDaedalus()->getAlivePlayers();
        $alivePlayers = $alivePlayers->filter(static fn (Player $player) => self::playerCanBeFlirted($NPC, $player))
            ->filter(static fn (Player $player) => self::targetNotTooFar($NPC, $player));

        if ($alivePlayers->isEmpty() === false) {
            $arrayCollection->add($this->randomService->getRandomElement($alivePlayers->toArray()));
        }
    }

    private static function playerCanBeRecycled(Drone $npc, Player $player): bool
    {
        return $player->isDead() && $player->isInARoom() && $npc->getStringFromMemory($player->getLogName()) !== 'recycled';
    }

    private static function playerCanBeFlirted(Drone $npc, Player $player): bool
    {
        return $player->isAlive() && $npc->getStringFromMemory($player->getLogName()) !== 'flirted';
    }

    private static function targetNotTooFar(Drone $npc, StatusHolderInterface $target): bool
    {
        return RoomEnum::getChebyshevDistance($npc->getPlace()->getName(), $target->getPlace()->getName()) < 30;
    }

    private static function targetNotVisitedLess4Cycles(Drone $npc, StatusHolderInterface $target): bool
    {
        return $npc->getIntFromMemory($target->getName()) + 0 < self::getCurrentCycle($npc);
    }

    private static function getCurrentCycle(Drone $npc): int
    {
        return $npc->getDaedalus()->getDay() * $npc->getDaedalus()->getNumberOfCyclesPerDay() + $npc->getDaedalus()->getCycle();
    }

    private function getNewTarget(Drone $NPC, \DateTime $time): ?Status
    {
        if ($this->randomService->isSuccessful($this->idleChance)) {
            return null;
        }

        $target = $this->getDeadPlayer($NPC);

        if ($target === null) {
            $targets = new ArrayCollection();

            $this->getNeron($NPC, $targets);
            $this->getLivingPlayer($NPC, $targets);

            if ($targets->isEmpty()) {
                return null;
            }

            $target = $this->randomService->getRandomElement($targets->toArray());
        }

        return $this->statusService->createStatusFromName(
            EquipmentStatusEnum::EVIL_DRONE_TARGET,
            $NPC,
            [$NPC->getName()],
            $time,
            $target
        );
    }
}
