<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

interface RandomServiceInterface
{
    public function random(int $min, int $max): int;

    public function randomPercent(): int;

    public function getPlayerInRoom(Room $room): Player;

    public function getPlayerInShip(Daedalus $ship): Player;

    public function getPlayerInDaedalus(Daedalus $ship): Player;

    public function getItemInRoom(Room $room): GameItem;

    public function getRandomElements(array $array, int $number = 1): array;
}
