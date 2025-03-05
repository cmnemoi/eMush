<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Hunter\TestDoubles;

use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Repository\HunterRepositoryInterface;

final class InMemoryHunterRepository implements HunterRepositoryInterface
{
    /** @var Hunter[] */
    private array $hunters = [];

    public function findByIdOrThrow(int $id): Hunter
    {
        return $this->hunters[$id] ?? throw new \RuntimeException("Hunter not found for id {$id}");
    }

    public function findOneByTargetOrThrow(HunterTarget $hunterTarget): Hunter
    {
        foreach ($this->hunters as $hunter) {
            if ($hunter->getTarget()->getId() === $hunterTarget->getId()) {
                return $hunter;
            }
        }

        throw new \RuntimeException('Hunter not found');
    }

    public function save(Hunter $hunter): void
    {
        $this->setupId($hunter);
        $this->hunters[$hunter->getId()] = $hunter;
    }

    public function findOneByDaedalusId(int $daedalusId): ?Hunter
    {
        foreach ($this->hunters as $hunter) {
            if ($hunter->getDaedalus()->getId() === $daedalusId) {
                return $hunter;
            }
        }

        return null;
    }

    private function setupId(Hunter $hunter): void
    {
        $reflectionProperty = new \ReflectionProperty(Hunter::class, 'id');
        $reflectionProperty->setValue($hunter, (int) spl_object_hash($hunter));
    }
}
