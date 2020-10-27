<?php

namespace Mush\Game\Service;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Item;


class RandomService implements RandomServiceInterface
{
    public function random(int $min, int $max): int
    {
        return rand($min, $max);
    }

    public function getPlayerInRoom($room): Player
    {
      if (!($room instanceof Room))
      {
        return new Error('getPlayerInRoom: argument is not a room')
      }
      else if (!sizeof($room->getPlayers))
      {
        return new Error('getPlayerInRoom: room is empty')
      }

      return $room->getPlayers[rand(0, sizeof($room->getPlayers) - 1)];
    }

    public function getPlayerInShip($ship): Player
    {
      if (!($ship instanceof Daedalus))
      {
        return new Error('getPlayerInShip: argument is not a ship')
      }
      else if (!sizeof($ship->getPlayers))
      {
        return new Error('getPlayerInShip: ship is empty')
      }

      return $ship->getPlayers[rand(0, sizeof($ship->getPlayers) - 1)];
    }

    public function getPlayerInDaedalus($ship): Player
    {
      return $this->getPlayerInShip($ship);
    }

    public function getItemInRoom($room): Item
    {
      if (!($room instanceof Room))
      {
        return new Error('getItemInRoom: argument is not a room')
      }
      else if (!sizeof($room->getItems))
      {
        return new Error('getItemInRoom: room has no items')
      }

      return $room->getItems[rand(0, sizeof($ship->getItems) - 1)];
    }

}
