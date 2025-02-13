<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;

final class InMemoryNeronVersionRepository implements NeronVersionRepositoryInterface
{
    /** @var NeronVersion[] */
    private array $neronVersions = [];

    public function deleteByDaedalusId(int $daedalusId): void
    {
        unset($this->neronVersions[$daedalusId]);
    }

    public function findByDaedalusIdOrThrow(int $daedalusId): NeronVersion
    {
        if (!isset($this->neronVersions[$daedalusId])) {
            throw new \RuntimeException("Daedalus with ID {$daedalusId} does not have a NERON version");
        }

        return $this->neronVersions[$daedalusId];
    }

    public function save(NeronVersion $neronVersion): void
    {
        $this->neronVersions[$neronVersion->getDaedalusId()] = $neronVersion;
    }

    public function clear(): void
    {
        $this->neronVersions = [];
    }
}
