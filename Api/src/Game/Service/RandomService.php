<?php

namespace Mush\Game\Service;

use Error;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

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

    public function isSuccessful(int $successRate): bool
    {
        return $this->randomPercent() <= $successRate;
    }

    public function getRandomPlayer(PlayerCollection $players): Player
    {
        if ($players->isEmpty()) {
            throw new Error('getRandomPlayer: collection is empty');
        }

        return current($this->getRandomElements($players->toArray()));
    }

    public function getPlayerInRoom(Place $place): Player
    {
        return $this->getRandomPlayer($place->getPlayers()->getPlayerAlive());
    }

    public function getAlivePlayerInDaedalus(Daedalus $ship): Player
    {
        return $this->getRandomPlayer($ship->getPlayers()->getPlayerAlive());
    }

    public function getItemInRoom(Place $place): GameItem
    {
        if ($place->getEquipments()->isEmpty()) {
            throw new Error('getItemInRoom: room has no items');
        }

        $items = $place->getEquipments()->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        return current($this->getRandomElements($items->toArray()));
    }

    public function getRandomElements(array $array, int $number = 1): array
    {
        if (count($array) < $number || empty($array)) {
            return [];
        }

        $result = [];
        for ($i = 0; $i < $number; ++$i) {
            $keysNotPicked = array_values(array_diff(array_keys($array), array_keys($result)));

            $key = $keysNotPicked[$this->random(0, count($keysNotPicked) - 1)];
            $result[$key] = $array[$key];
        }

        return $result;
    }

    // This function takes an array [element => proba%] as input and send back an array
    // Instead of proba relative ponderation also work
    public function getSingleRandomElementFromProbaArray(array $array): string
    {
        if (count($array) < 1) {
            throw new Error('getSingleRandomElement: array is not large enough');
        }

        //first create a cumulative form of the array
        $cumuProba = 0;
        foreach ($array as $event => $proba) {
            if (!is_int($proba)) {
                throw new Error('Proba weight should be provided as integers');
            }

            $cumuProba = $cumuProba + $proba;
            $array[$event] = $cumuProba;
        }

        if ($cumuProba === 0) {
            throw new Error('getSingleRandomElement: only 0 proba element in array');
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
        if (count($array) < $number) {
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
