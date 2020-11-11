<?php

namespace Mush\Game\Service;

use Error;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

class RandomService implements RandomServiceInterface
{
    public function random(int $min, int $max): int
    {
        return rand($min, $max);
    }

    public function randomPercent(): int
    {
        return $this->random(1, 100);
    }

    public function getPlayerInRoom(Room $room): Player
    {
        if ($room->getPlayers()->isEmpty()) {
            throw new Error('getPlayerInRoom: room is empty');
        }

        return $room->getPlayers()->get($this->random(0, $room->getPlayers()->count() - 1));
    }

    public function getPlayerInShip(Daedalus $ship): Player
    {
        if ($ship->getPlayers()->isEmpty()) {
            throw new Error('getPlayerInShip: ship is empty');
        }

        return $ship->getPlayers()->get($this->random(0, $ship->getPlayers()->count() - 1));
    }

    public function getPlayerInDaedalus(Daedalus $ship): Player
    {
        return $this->getPlayerInShip($ship);
    }

    public function getItemInRoom(Room $room): GameItem
    {
        if ($room->getItems()->isEmpty()) {
            throw new Error('getItemInRoom: room has no items');
        }

        return $room->getItems()->get($this->random(0, $room->getItems()->count() - 1));
    }
    
    public function getRandomElements(array $array, int $number = 1): array
    {
        if (count($array) < $number) {
            throw new Error('getRandomElements: array is not large enough');
        }
        if ($number===0) {
            return [];
        } elseif ($number>1) {
            return array_rand(array_flip($array), $number);
        } else {
            return [array_rand(array_flip($array), $number)];
        }
    }
}
