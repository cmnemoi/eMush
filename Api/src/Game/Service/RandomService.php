<?php

namespace Mush\Game\Service;

use Error;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Player\Entity\Collection\PlayerCollection;
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

    public function isSuccessfull(int $successRate): bool
    {
        return $this->randomPercent() <= $successRate;
    }

    public function getRandomPlayer(PlayerCollection $players): Player
    {
        if ($players->isEmpty()) {
            throw new Error('getRandomPlayer: collection is empty');
        }

        return $players->get($this->random(0, $players->count() - 1));
    }

    public function getPlayerInRoom(Room $room): Player
    {
        return $this->getRandomPlayer($room->getPlayers());
    }

    public function getAlivePlayerInDaedalus(Daedalus $ship): Player
    {
        return $this->getRandomPlayer($ship->getPlayers()->getPlayerAlive());
    }

    public function getItemInRoom(Room $room): GameItem
    {
        if ($room->getEquipments()->isEmpty()) {
            throw new Error('getItemInRoom: room has no items');
        }

        return $room->getEquipments()->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem)
            ->get($this->random(0, $room->getEquipments()->count() - 1));
    }

    public function getRandomElements(array $array, int $number = 1): array
    {
        if (count($array) < $number || empty($array)) {
            throw new Error('getRandomElements: array is not large enough');
        }
        $randomKeys = array_rand($array, $number);
        if (is_array($randomKeys)) {
            return array_diff_key($array, array_flip($randomKeys));
        } else {
            return [$randomKeys => $array[$randomKeys]];
        }
    }

    // This function takes an array [element => proba%] as input and send back an array
    // Instead of proba relative ponderation also work
    public function getSingleRandomElementFromProbaArray(array $array): string
    {
        if (count($array) === 0) {
            throw new Error('getSingleRandomElement: array is not large enough');
        }
        //first create a cumulative form of the array
        $cumuProba = 0;
        foreach ($array as $event => $proba) {
            $cumuProba = $cumuProba + $proba;
            $array[$event] = $cumuProba;
        }

        $probaLim = $this->random(0, $cumuProba);

        $pickedElement = array_filter($array, function ($n) use ($probaLim) {
            return $n >= $probaLim;
        });

        return key($pickedElement);
    }

    // This function takes an array [element => proba%] as input and send back an array
    public function getRandomElementsFromProbaArray(array $array, int $number): array
    {
        if (count(array_filter($array, function ($weight) {return $weight !== 0; }) < $number)) {
            throw new Error('getRandomElements: array is not large enough');
        }
        $randomElements = [];
        for ($i = 0; $i < $number; ++$i) {
            $randomElements[$i] = $this->getSingleRandomElementFromProbaArray(
                array_diff_key($array, array_flip($randomElements))
            );
        }

        return $randomElements;
    }
}
