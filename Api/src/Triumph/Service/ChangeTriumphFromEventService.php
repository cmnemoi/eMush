<?php

declare(strict_types=1);

namespace Mush\Triumph\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Event\TriumphChangedEvent;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;

final class ChangeTriumphFromEventService
{
    public function __construct(
        private CycleServiceInterface $cycleService,
        private EventServiceInterface $eventService,
        private StatusServiceInterface $statusService,
        private StatusServiceInterface $statusService,
        private TriumphConfigRepositoryInterface $triumphConfigRepository,
    ) {}

    public function execute(TriumphSourceEventInterface $event): void
    {
        $triumphConfigs = $this->triumphConfigRepository->findAllByTargetedEvent($event);

        foreach ($triumphConfigs as $triumphConfig) {
            $event->getTriumphTargets($triumphConfig)->map(
                fn (Player $player) => $this->addTriumphToPlayer($triumphConfig, $player)
            );
        }
    }

    private function addTriumphToPlayer(TriumphConfig $triumphConfig, Player $player): void
    {
        if ($this->isPreventedByRegression($triumphConfig, $player)) {
            return;
        }

        $quantity = $this->computeTriumphForPlayer($triumphConfig, $player);

        // Don't call triumph changed by 0 event unless the config explicitly states 0 triumph change
        if ($quantity === 0 && $triumphConfig->getQuantity() !== 0) {
            return;
        }

        $player->addTriumph($quantity);
        $this->recordTriumphGain($triumphConfig, $player, $quantity);

        $this->eventService->callEvent(
            new TriumphChangedEvent($player, $triumphConfig, $quantity),
            TriumphChangedEvent::class,
        );
    }

    private function computeTriumphForPlayer(TriumphConfig $triumphConfig, Player $player): int
    {
        return match ($triumphConfig->getName()) {
            TriumphEnum::CYCLE_MUSH_LATE => $this->computeNewMushTriumph($player->getDaedalus(), $triumphConfig->getQuantity()),
            TriumphEnum::EDEN_MUSH_INTRUDER, TriumphEnum::SOL_MUSH_INTRUDER => $player->getDaedalus()->getMushPlayers()->getPlayerAlive()->count() * $triumphConfig->getQuantity(),
            TriumphEnum::EDEN_ONE_MAN => $player->getDaedalus()->getAlivePlayers()->count() * $triumphConfig->getQuantity(),
            TriumphEnum::PILGRED_MOTHER => $player->getDaedalus()->getProjectByName(ProjectName::PILGRED)->getNumberOfProgressStepsCrossedForThreshold(20) * $triumphConfig->getQuantity(),
            default => $triumphConfig->getQuantity(),
        };
    }

    private function recordTriumphGain(TriumphConfig $triumphConfig, Player $player, int $quantity): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $closedPlayer->recordTriumphGain($triumphConfig->getLogName(), $quantity);
    }

    private function computeNewMushTriumph(Daedalus $daedalus, int $triumphChangePerCycle): int
    {
        $startingTriumph = $daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow(TriumphEnum::MUSH_INITIAL_BONUS)->getQuantity();
        $filledAt = $daedalus->getFilledAt() ?? new \DateTime();
        $nextCycleAt = $this->cycleService->getDateStartNextCycle($daedalus);
        $cyclesLasted = $this->cycleService->getNumberOfCycleElapsed($filledAt, $nextCycleAt, $daedalus->getDaedalusInfo());

        return max($startingTriumph + $cyclesLasted * $triumphChangePerCycle, 0);
    }

    private function isPreventedByRegression(TriumphConfig $triumphConfig, Player $player): bool
    {
        if ($triumphConfig->isRegressive()) {
            return false;
        }

        $timesTriumphChanged = $this->statusService->createOrIncrementChargeStatus(
            name: PlayerStatusEnum::PERSONAL_TRIUMPH_REGRESSION,
            holder: $player
        )->getCharge();
        $divisor = 1 + (int) ($timesTriumphChanged / $triumphConfig->getRegressiveFactor());

        return $timesTriumphChanged % $divisor !== 0;
    }
}
