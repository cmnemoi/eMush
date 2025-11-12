<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;

final class InMemoryRebelBaseRepository implements RebelBaseRepositoryInterface
{
    private array $rebelBases = [];

    public function areAllRebelBasesDecoded(int $daedalusId): bool
    {
        $this->rebelBases = array_filter(
            $this->rebelBases,
            static fn (RebelBase $rebelBase) => $rebelBase->getDaedalusId() === $daedalusId && !$rebelBase->isDecoded()
        );

        return \count($this->rebelBases) === 0;
    }

    public function deleteAllByDaedalusId(int $daedalusId): void
    {
        $this->rebelBases = array_filter(
            $this->rebelBases,
            static fn (RebelBase $rebelBase) => $rebelBase->getDaedalusId() !== $daedalusId
        );
    }

    public function findAllByDaedalusId(int $daedalusId): array
    {
        return array_filter(
            $this->rebelBases,
            static fn (RebelBase $rebelBase) => $rebelBase->getDaedalusId() === $daedalusId
        );
    }

    public function findAllContactingRebelBases(int $daedalusId): array
    {
        return array_filter(
            $this->rebelBases,
            static fn (RebelBase $rebelBase) => $rebelBase->isContacting() && $rebelBase->getDaedalusId() === $daedalusId
        );
    }

    public function findAllDecodedRebelBases(int $daedalusId): array
    {
        return array_filter(
            $this->rebelBases,
            static fn (RebelBase $rebelBase) => $rebelBase->isDecoded() && $rebelBase->getDaedalusId() === $daedalusId
        );
    }

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, RebelBaseEnum $name): RebelBase
    {
        $rebelBase = array_filter(
            $this->rebelBases,
            static fn (RebelBase $rebelBase) => $rebelBase->getDaedalusId() === $daedalusId && $rebelBase->getName() === $name
        );

        if (\count($rebelBase) === 0) {
            throw new \RuntimeException("No rebel base found for daedalus {$daedalusId} and name {$name->toString()}");
        }

        return $rebelBase[0];
    }

    public function findMostRecentContactingRebelBase(int $daedalusId): ?RebelBase
    {
        $rebelBases = $this->findAllContactingRebelBases($daedalusId);
        if (\count($rebelBases) === 0) {
            return null;
        }

        $mostRecentContactingRebelBase = $rebelBases[0];
        foreach ($rebelBases as $rebelBase) {
            if ($rebelBase->getContactStartDateOrThrow() > $mostRecentContactingRebelBase->getContactStartDateOrThrow()) {
                $mostRecentContactingRebelBase = $rebelBase;
            }
        }

        return $mostRecentContactingRebelBase;
    }

    public function findNextContactingRebelBase(int $daedalusId): ?RebelBase
    {
        $rebelBases = $this->findAllByDaedalusId($daedalusId);

        $nextContactingRebelBase = $rebelBases[0];
        if ($nextContactingRebelBase->isNotContacting()) {
            return $nextContactingRebelBase;
        }

        foreach ($rebelBases as $rebelBase) {
            if ($rebelBase->isNotContacting() && $rebelBase->getContactOrder() < $nextContactingRebelBase->getContactOrder()) {
                $nextContactingRebelBase = $rebelBase;
            }
        }

        return $nextContactingRebelBase;
    }

    public function hasNoContactingRebelBase(int $daedalusId): bool
    {
        return \count($this->findAllContactingRebelBases($daedalusId)) === 0;
    }

    public function save(RebelBase $rebelBase): void
    {
        $this->rebelBases[hash('crc32b', serialize($rebelBase))] = $rebelBase;
    }
}
