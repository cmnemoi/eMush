<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\AIHandlerEnum;
use Mush\Equipment\NPCTasks\Schrodinger\MoveAwayFromPeopleTask;
use Mush\Equipment\NPCTasks\Schrodinger\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\NPCTasks\Schrodinger\MoveTowardsOwnerTask;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;

/**
 * Handles Schrodinger the cat.
 * This NPC tries to get closer to its owner (Raluca), but if none is found (i.e. she is dead), it'll get grumpy and try to avoid people.
 * See the flowchart for behavior details.
 */
class CatTasksHandler extends AbstractAiHandler
{
    private const int FAVORED_TASK = 70;
    protected string $name = AIHandlerEnum::CAT->value;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private MoveTowardsOwnerTask $moveTowardsOwner,
        private MoveAwayFromPeopleTask $moveAwayFromPeople,
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoom,
    ) {}

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        if ($NPC->getDaedalus()->getAlivePlayers()->hasPlayerWithStatus(PlayerStatusEnum::CAT_OWNER)) {
            if (!$NPC->getPlace()->hasAlivePlayerWithStatus(PlayerStatusEnum::CAT_OWNER)) {
                $this->d100Roll->isSuccessful(self::FAVORED_TASK)
                    ? $this->moveTowardsOwner->execute($NPC, $time)
                    : $this->moveInRandomAdjacentRoom->execute($NPC, $time);
            }
        } else {
            if ($NPC->getPlace()->getNumberOfPlayersAlive() > 0 && $this->d100Roll->isSuccessful(self::FAVORED_TASK)) {
                $this->moveAwayFromPeople->execute($NPC, $time);
            } elseif ($this->d100Roll->isAFailure(self::FAVORED_TASK)) {
                $this->moveInRandomAdjacentRoom->execute($NPC, $time);
            }
        }
    }
}
