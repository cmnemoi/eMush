<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\AIHandlerEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\NPCTasks\Schrodinger\MoveAwayFromPeopleTask;
use Mush\Equipment\NPCTasks\Schrodinger\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\NPCTasks\Schrodinger\MoveTowardsOwnerTask;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * Handles the baby skinner.
 * If it protect someone, it does not move on it's own but it has 10% to bite someone or 30% to sleep
 * If it does not protect anyone, it try to move away from peoples 70% of the time or sleep.
 */
class BabySkinnerTasksHandler extends AbstractAiHandler
{
    private const int FAVORED_TASK = 70;
    private const int BITE_CHANCE = 15;
    private const int BITE_DMG = -2;
    protected string $name = AIHandlerEnum::BABY_SKINNER->value;

    public function __construct(
        private RandomServiceInterface $randomService,
        private MoveTowardsOwnerTask $moveTowardsOwner,
        private MoveAwayFromPeopleTask $moveAwayFromPeople,
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoom,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
        private EventServiceInterface $eventService,
    ) {}

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        // we check if the pet protect someone
        $status = $this->statusService->getByTargetAndName($NPC, PlayerStatusEnum::PROTECTED_BY_PET);
        if ($status) {
            // if yes then 70% * 15% to bite. Else 30% to sleep
            if ($this->randomService->isSuccessful(self::FAVORED_TASK)) {
                if ($this->randomService->isSuccessful(self::BITE_CHANCE)) {
                    $this->biteTask($NPC, $status, $time);
                }
            } else {
                $this->sleepTask($NPC, $time);
            }
        // if no then will flee if with peoples and 30% to sleep if not
        } else {
            if ($NPC->getPlace()->getNumberOfPlayersAlive() > 0 && $this->randomService->isSuccessful(self::FAVORED_TASK)) {
                $this->moveAwayFromPeople->execute($NPC, $time);
            } elseif ($this->randomService->isSuccessful(self::FAVORED_TASK)) {
                return;
            } else {
                $this->sleepTask($NPC, $time);
            }
        }
    }

    public function sleepTask(GameEquipment $NPC, \DateTime $time): void
    {
        $this->roomLogService->createLog(
            LogEnum::BABY_SKINNER_SLEEP,
            $NPC->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            dateTime: $time
        );
    }

    public function biteTask(GameEquipment $NPC, Status $status, \DateTime $time): void
    {
        $infectedStatus = $NPC->getStatusByName(EquipmentStatusEnum::BABY_SKINNER_INFECTED);

        // get the target
        $target = $this->getRandomPlayer($status, $infectedStatus !== null);
        if (!$target) {
            return;
        }

        // deal dmg
        $playerVariableEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::HEALTH_POINT,
            self::BITE_DMG,
            [ItemEnum::TREASURE_HUNT_PET],
            $time
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        // give spore if infected
        if ($infectedStatus !== null) {
            $playerVariableEventInfection = new PlayerVariableEvent(
                $target,
                PlayerVariableEnum::SPORE,
                1,
                [EquipmentStatusEnum::BABY_SKINNER_INFECTED],
                $time
            );
            $playerVariableEventInfection->setGameEquipment($NPC);
            $this->eventService->callEvent($playerVariableEventInfection, VariableEventInterface::CHANGE_VARIABLE);
        }

        // make the log
        $this->roomLogService->createLog(
            LogEnum::BABY_SKINNER_BITE,
            $NPC->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            parameters: ['target_character' => $target->getLogName()],
            dateTime: $time
        );
    }

    private function getRandomPlayer(Status $status, bool $isMush): ?Player
    {
        $owner = $status->getPlayerOwnerOrThrow();
        $players = $owner->getPlace()->getAlivePlayersExcept($owner);

        if ($isMush) {
            $players = $players->getHumanPlayer();
        }

        if ($players->isEmpty()) {
            return null;
        }

        return $this->randomService->getRandomPlayer($players);
    }
}
