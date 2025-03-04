<?php

namespace Mush\Tests;

use Mush\Player\Entity\Player;

final readonly class RoomLogDto
{
    public function __construct(
        public Player $player,
        public string $log,
        public string $visibility,
        public bool $inPlayerRoom = true,
    ) {}

    public function toArray(): array
    {
        $params = [
            'daedalusInfo' => $this->player->getDaedalusInfo(),
            'log' => $this->log,
            'visibility' => $this->visibility,
        ];

        if ($this->inPlayerRoom) {
            $params['place'] = $this->player->getPlace()->getName();
            $params['playerInfo'] = $this->player->getPlayerInfo();
        }

        return $params;
    }
}
