<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\PlayerStatistics;
use Mush\Daedalus\Enum\FunFactEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
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
                $funFacts[$funFact] = $this->getPlayersFromGreatestStatisticValue($funFact, $closedDaedalus);
            } elseif (FunFactEnum::looksForSmallestStatValue($funFact)) {
                $funFacts[$funFact] = $this->getPlayersFromSmallestStatisticValue($funFact, $closedDaedalus);
            } else {
                $funFacts[$funFact] = $this->getPlayersFromOtherMetrics($funFact, $closedDaedalus);
            }
        }

        /** @var PlayerCollection $players */
        foreach ($funFacts as $key => $players) {
            if ($players->isEmpty()) {
                unset($funFacts[$key]);
            }
        }

        $daedalusInfo->setFunFacts($funFacts);
    }

    private function getPlayersFromGreatestStatisticValue(string $funFact, ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        $statisticValues = $this->getValueSetFromFunFact($funFact, $closedDaedalus);
        $greatestValue = $this->getGreatestNumberFromArray($statisticValues->toArray());
        if ($greatestValue === null || $greatestValue <= 0) {
            return new PlayerCollection();
        }

        return new PlayerCollection($closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getNumberStatisticForFunFact($funFact) === $greatestValue)->toArray());
    }

    private function getPlayersFromSmallestStatisticValue(string $funFact, ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        $statisticValues = $this->getValueSetFromFunFact($funFact, $closedDaedalus);
        $smallestValue = $this->getSmallestNumberFromArray($statisticValues->toArray());

        return new PlayerCollection($closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getNumberStatisticForFunFact($funFact) === $smallestValue)->toArray());
    }

    private function getPlayersFromOtherMetrics(string $funFact, ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        return match ($funFact) {
            FunFactEnum::UNLUCKIER_TECHNICIAN => $this->getUnluckiestTechnicianAsCollection($closedDaedalus),
            FunFactEnum::EARLIEST_DEATH => $this->getFirstToDiePlayerAsCollection($closedDaedalus),
            FunFactEnum::DEAD_DURING_SLEEP => new PlayerCollection($closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->hasDiedDuringSleep())->toArray()),
            FunFactEnum::UNSTEALTHIEST => $this->getNotAssassinatedUnstealthiestPlayerAsCollection($closedDaedalus),
            FunFactEnum::UNSTEALTHIEST_AND_KILLED => $this->getAssassinatedUnstealthiestPlayerAsCollection($closedDaedalus),
            default => throw new \LogicException('Unknown fun fact to handle'),
        };
    }

    private function getUnluckiestTechnicianAsCollection(ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        $candidates = $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getTechFails() > 0);
        $failRates = $candidates->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getTechFailRate());
        $highestRate = $this->getGreatestNumberFromArray($failRates->toArray());
        if ($highestRate === null || $highestRate <= 0) {
            return new PlayerCollection();
        }

        return new PlayerCollection($candidates->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getTechFails() >= $highestRate)->toArray());
    }

    private function getFirstToDiePlayerAsCollection(ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        $deadPlayers = $closedDaedalus->getPlayers()->filter(static fn (ClosedPlayer $player) => !\in_array($player->getEndCause(), EndCauseEnum::getNotDeathEndCauses()->toArray(), true));
        $deathTimestamps = $deadPlayers->map(static fn (ClosedPlayer $player) => $player->getFinishedAtOrThrow()->getTimestamp());
        $earliestDeath = $this->getSmallestNumberFromArray($deathTimestamps->toArray());

        return new PlayerCollection($deadPlayers->filter(static fn (ClosedPlayer $player) => $player->getFinishedAtOrThrow()->getTimestamp() === $earliestDeath)->toArray());
    }

    private function getValueSetFromFunFact(string $funFact, ClosedDaedalus $closedDaedalus): ArrayCollection
    {
        $playersStatistics = $closedDaedalus->getPlayers()->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics());

        return $playersStatistics->map(static fn (PlayerStatistics $stat) => $stat->getNumberStatisticForFunFact($funFact));
    }

    private function getNotAssassinatedUnstealthiestPlayerAsCollection(ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        $candidates = $closedDaedalus->getPlayers()->filter(fn (ClosedPlayer $player) => $this->wasAssassinated($player) === false);
        $unstealthyValues = $candidates->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken());
        $greatestValue = $this->getGreatestNumberFromArray($unstealthyValues->toArray());
        if ($greatestValue === null || $greatestValue <= 0) {
            return new PlayerCollection();
        }

        return new PlayerCollection($candidates->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken() === $greatestValue)->toArray());
    }

    private function getAssassinatedUnstealthiestPlayerAsCollection(ClosedDaedalus $closedDaedalus): PlayerCollection
    {
        $candidates = $closedDaedalus->getPlayers()->filter(fn (ClosedPlayer $player) => $this->wasAssassinated($player));
        $unstealthyValues = $candidates->map(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken());
        $greatestValue = $this->getGreatestNumberFromArray($unstealthyValues->toArray());
        if ($greatestValue === null || $greatestValue <= 0) {
            return new PlayerCollection();
        }

        return new PlayerCollection($candidates->filter(static fn (ClosedPlayer $player) => $player->getPlayerInfo()->getStatistics()->getUnstealthActionsTaken() === $greatestValue)->toArray());
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
