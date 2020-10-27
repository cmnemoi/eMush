<?php

namespace Mush\Game\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\Item;

interface RandomServiceInterface
{
    public function random(int $min, int $max): int;
    public function getPlayerInRoom($room): Player;
    public function getPlayerInShip($ship): Player;
    public function getPlayerInDaedalus($ship): Player;
    public function getItemInRoom($room): Item;
}
