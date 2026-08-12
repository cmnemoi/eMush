<?php

declare(strict_types=1);

namespace Mush\MetaGame\ViewModel;

use Mush\Game\ViewModel\ViewModelInterface;

final readonly class GameCountersViewModel implements ViewModelInterface
{
    public function __construct(
        public int $daedalusesInGame,
        public int $mushKilled,
        public int $messagesSent,
        public int $expeditionsStarted,
    ) {}

    public static function fromQueryRow(array $row): self
    {
        return new self(
            (int) $row['daedaluses_in_game'],
            (int) $row['mush_killed'],
            (int) $row['messages_sent'],
            (int) $row['expeditions_started'],
        );
    }

    public function toArray(): array
    {
        return [
            'daedalusesInGame' => $this->daedalusesInGame,
            'mushKilled' => $this->mushKilled,
            'messagesSent' => $this->messagesSent,
            'expeditionsStarted' => $this->expeditionsStarted,
        ];
    }
}
