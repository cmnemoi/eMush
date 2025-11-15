<?php

declare(strict_types=1);

namespace Mush\Achievement\ValueObject;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;

final class CycleCounts
{
    /** @var array<string, int> */
    private array $counts = [];

    public function getForCharacter(string $character): int
    {
        return $this->counts[$character] ?? 0;
    }

    public function incrementForPlayer(Player $player, int $increment = 1): self
    {
        $character = $player->isMush() ? CharacterEnum::MUSH : $player->getName();
        $this->counts[$character] = ($this->counts[$character] ?? 0) + $increment;

        return $this;
    }

    /** @param array<string, int> $counts */
    public static function fromArray(array $counts): self
    {
        $instance = new self();
        $instance->counts = $counts;

        return $instance;
    }

    /** @return array<string, int> */
    public function toArray(): array
    {
        return $this->counts;
    }
}
