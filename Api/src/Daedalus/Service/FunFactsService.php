<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\PlayerStatistics;
use Mush\Daedalus\Enum\FunFactEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Enum\EndCauseEnum;

final class FunFactsService implements FunFactsServiceInterface
{
    public function generateForDaedalusInfo(DaedalusInfo $daedalusInfo): void
    {
        $closedDaedalus = $daedalusInfo->getClosedDaedalus();
        $closedPlayers = $closedDaedalus->getPlayers();
        if ($closedPlayers->isEmpty()) {
            return;
        }

        $funFacts = [];

        foreach (FunFactEnum::getAll() as $funFact) {
            if (FunFactEnum::looksForGreatestStatValue($funFact)) {
                $funFacts[$funFact] = $this->getPlayerNamesFromGreatestStatisticValue($funFact, $closedDaedalus);
            } elseif (FunFactEnum::looksForSmallestStatValue($funFact)) {
                $funFacts[$funFact] = $this->getPlayerNamesFromSmallestStatisticValue($funFact, $closedDaedalus);
            } else {
                $funFacts[$funFact] = $this->getPlayerNamesFromOtherMetrics($funFact, $closedDaedalus);
            }
        }

        foreach ($funFacts as $key => $players) {
            if (\count($players) === 0) {
                unset($funFacts[$key]);
            }
        }

        $daedalusInfo->setFunFacts($funFacts);
    }

    private function getPlayerNamesFromGreatestStatisticValue(string $funFact, ClosedDaedalus $closedDaedalus): array
    {
        $statisticValues = $this->getValueSetFromFunFact($funFact, $closedDaedalus);
        $greatestValue = $this->getGreatestNumberFromArray($statisticValues->toArray());
        if ($greatestValue === null || $greatestValue <= 0) {
            return [];
        }

        return $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getNumberStatisticForFunFact($funFact) === $greatestValue)
            ->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray();
    }

    private function getPlayerNamesFromSmallestStatisticValue(string $funFact, ClosedDaedalus $closedDaedalus): array
    {
        $statisticValues = $this->getValueSetFromFunFact($funFact, $closedDaedalus);
        $smallestValue = $this->getSmallestNumberFromArray($statisticValues->toArray());

        return $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getNumberStatisticForFunFact($funFact) === $smallestValue)
            ->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray();
    }

    private function getPlayerNamesFromOtherMetrics(string $funFact, ClosedDaedalus $closedDaedalus): array
    {
        return match ($funFact) {
            FunFactEnum::UNLUCKIER_TECHNICIAN => $this->getUnluckiestTechnicianAsNameArray($closedDaedalus),
            FunFactEnum::EARLIEST_DEATH => $this->getFirstToDiePlayerAsNameArray($closedDaedalus),
            FunFactEnum::DEAD_DURING_SLEEP => $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->hasDiedDuringSleep())->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray(),
            FunFactEnum::UNSTEALTHIEST => $this->getNotAssassinatedUnstealthiestPlayerAsNameArray($closedDaedalus),
            FunFactEnum::UNSTEALTHIEST_AND_KILLED => $this->getAssassinatedUnstealthiestPlayerAsNameArray($closedDaedalus),
            default => throw new \LogicException('Unknown fun fact to handle'),
        };
    }

    private function getUnluckiestTechnicianAsNameArray(ClosedDaedalus $closedDaedalus): array
    {
        $candidates = $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getTechFails() > 0);
        $failRates = $candidates->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getTechFailRate());
        $highestRate = $this->getGreatestNumberFromArray($failRates->toArray());
        if ($highestRate === null || $highestRate <= 0) {
            return [];
        }

        return $candidates->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getTechFails() >= $highestRate)->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray();
    }

    private function getFirstToDiePlayerAsNameArray(ClosedDaedalus $closedDaedalus): array
    {
        $deadPlayers = $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => !\in_array($player->getEndCause(), EndCauseEnum::getNotDeathEndCauses()->toArray(), true));
        $deathTimestamps = $deadPlayers->map(static fn (ClosedPlayer $player) => $player->getFinishedAtOrThrow()->getTimestamp());
        $earliestDeath = $this->getSmallestNumberFromArray($deathTimestamps->toArray());

        return $deadPlayers->filter(static fn (ClosedPlayer $player) => $player->getFinishedAtOrThrow()->getTimestamp() === $earliestDeath)->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray();
    }

    private function getValueSetFromFunFact(string $funFact, ClosedDaedalus $closedDaedalus): ArrayCollection
    {
        $playersStatistics = $closedDaedalus->getPlayers()->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics());

        return $playersStatistics->map(static fn (PlayerStatistics $stat) => $stat->getNumberStatisticForFunFact($funFact));
    }

    private function getNotAssassinatedUnstealthiestPlayerAsNameArray(ClosedDaedalus $closedDaedalus): array
    {
        $candidates = $closedDaedalus->getPlayers()->filter(fn (ClosedPlayer $player) => $this->wasAssassinated($player) === false);
        $unstealthyValues = $candidates->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken());
        $greatestValue = $this->getGreatestNumberFromArray($unstealthyValues->toArray());
        if ($greatestValue === null || $greatestValue <= 0) {
            return [];
        }

        return $candidates->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken() === $greatestValue)->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray();
    }

    private function getAssassinatedUnstealthiestPlayerAsNameArray(ClosedDaedalus $closedDaedalus): array
    {
        $candidates = $closedDaedalus->getPlayers()->filter(fn (ClosedPlayer $player) => $this->wasAssassinated($player));
        $unstealthyValues = $candidates->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken());
        $greatestValue = $this->getGreatestNumberFromArray($unstealthyValues->toArray());
        if ($greatestValue === null || $greatestValue <= 0) {
            return [];
        }

        return $candidates->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken() === $greatestValue)->map(static fn (ClosedPlayer $player) => $player->getLogName())->toArray();
    }

    private function wasAssassinated(ClosedPlayer $player): bool
    {
        return \in_array($player->getEndCause(), [
            EndCauseEnum::ASSASSINATED,
            EndCauseEnum::BEHEADED,
            EndCauseEnum::BLED,
            EndCauseEnum::INJURY,
            EndCauseEnum::ROCKETED,
        ], true);
    }

    private function getGreatestNumberFromArray(array $collection): null|float|int
    {
        $result = null;
        foreach ($collection as $number) {
            if ($result === null || $result < $number) {
                $result = $number;
            }
        }

        return $result;
    }

    private function getSmallestNumberFromArray(array $collection): ?int
    {
        $result = null;
        foreach ($collection as $number) {
            if ($result === null || $result > $number) {
                $result = $number;
            }
        }

        return $result;
    }
}
