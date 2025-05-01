<?php

declare(strict_types=1);

namespace Mush\Triumph\Repository;

use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Event\TriumphSourceEventInterface;

interface TriumphConfigRepositoryInterface
{
    /**
     * @return array<TriumphConfig>
     */
    public function findAllByTargetedEvent(TriumphSourceEventInterface $targetedEvent): array;
}
