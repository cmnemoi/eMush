<?php

declare(strict_types=1);

namespace Mush\MetaGame\ViewModel;

use Mush\Game\ViewModel\ViewModelInterface;

final readonly class FillingDaedalusViewModel implements ViewModelInterface
{
    public function __construct(
        public int $day,
        public int $cycle,
        public int $currentPlayers,
        public int $maxPlayers,
    ) {}

    public static function fromQueryRow(array $row): self
    {
        return new self(
            (int) $row['day'],
            (int) $row['cycle'],
            (int) $row['current_players'],
            (int) $row['max_players'],
        );
    }

    public function toArray(): array
    {
        return [
            'day' => $this->day,
            'cycle' => $this->cycle,
            'currentPlayers' => $this->currentPlayers,
            'maxPlayers' => $this->maxPlayers,
        ];
    }
}
