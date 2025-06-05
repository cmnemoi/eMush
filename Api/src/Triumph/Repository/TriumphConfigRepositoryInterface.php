<?php

declare(strict_types=1);

namespace Mush\Triumph\Repository;

use Mush\Player\Entity\Player;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Event\TriumphSourceEventInterface;

interface TriumphConfigRepositoryInterface
{
    /**
     * @return array<TriumphConfig>
     */
    public function findAllByTargetedEvent(TriumphSourceEventInterface $targetedEvent): array;

    /**
     * @return array<TriumphConfig>
     */
    public function findAllPersonalTriumphsForPlayer(Player $player): array;
}
