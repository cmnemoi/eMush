<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Achievement\ValueObject\CycleCounts;
use Mush\Player\Entity\Player;

#[ORM\Embeddable]
class PendingStatistics
{
    #[ORM\Column(type: 'json', nullable: false, options: ['default' => '[]'])]
    private array $characterCyclesCount = [];

    public function getCycleCounts(): CycleCounts
    {
        return CycleCounts::fromArray($this->characterCyclesCount);
    }

    public function incrementCyclesCountForPlayer(Player $player, int $increment = 1): void
    {
        $this->characterCyclesCount = CycleCounts::fromArray($this->characterCyclesCount)
            ->incrementForPlayer($player, $increment)
            ->toArray();
    }

    public function resetCycleCounts(): void
    {
        $this->characterCyclesCount = [];
    }
}
