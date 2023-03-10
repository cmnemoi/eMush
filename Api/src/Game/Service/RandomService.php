<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

class RandomService implements RandomServiceInterface
{
    private GameEquipmentRepository $gameEquipmentRepository;

    public function __construct(GameEquipmentRepository $gameEquipmentRepository)
    {
        $this->gameEquipmentRepository = $gameEquipmentRepository;
    }

    public function random(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public function randomPercent(): int
    {
        return $this->random(1, 100);
    }

    public function isSuccessful(int $successRate): bool
    {
        return $this->randomPercent() <= $successRate;
    }

    public function outputCriticalChances(int $successRate, int $criticalFailRate = 0, int $criticalSuccessRate = 0): string
    {
        $chance = $this->randomPercent();

        if ($criticalFailRate > $successRate || 100 - $criticalSuccessRate < $successRate) {
            throw new \Exception("criticalFailRate ({$criticalFailRate}) 
            must be lower than successRate ({$successRate}) and 
            100-criticalSuccessRate ({100 - $criticalSuccessRate}) higher than successRate ({$successRate})");
        }

        if ($chance <= $criticalFailRate) {
            return ActionOutputEnum::CRITICAL_FAIL;
        } elseif ($chance <= $successRate) {
            return ActionOutputEnum::FAIL;
        } elseif ($chance <= 100 - $criticalSuccessRate) {
            return ActionOutputEnum::SUCCESS;
        } elseif ($chance <= 100) {
            return ActionOutputEnum::CRITICAL_SUCCESS;
        }

        throw new \Exception("input percentages ({$chance}) should range between 0 and 100");
    }

    public function getRandomPlayer(PlayerCollection $players): Player
    {
        if ($players->isEmpty()) {
            throw new \Exception('getRandomPlayer: collection is empty');
        }

        return current($this->getRandomElements($players->toArray()));
    }

    public function getRandomDisease(PlayerDiseaseCollection $collection): PlayerDisease
    {
        if ($collection->isEmpty()) {
            throw new \Exception('getRandomDisease: collection is empty');
        }

        return current($this->getRandomElements($collection->toArray()));
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
            throw new \Exception("there isn't any item in this room");
        }

        $items = $place->getEquipments()->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        return current($this->getRandomElements($items->toArray()));
    }

    public function getRandomElements(array $array, int $number = 1): array
    {
        if (empty($array) || count($array) < $number) {
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
    public function getSingleRandomElementFromProbaArray(array $array): ?string
    {
        if (count($array) < 1) {
            return null;
        }

        // first create a cumulative form of the array
        $cumuProba = 0;
        foreach ($array as $event => $proba) {
            if (!is_int($proba)) {
                throw new \Exception('Proba weight should be provided as integers');
            }

            $cumuProba = $cumuProba + $proba;
            $array[$event] = $cumuProba;
        }

        if ($cumuProba === 0) {
            return null;
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
        $number = min($number, count($array));

        $randomElements = [];
        for ($i = 0; $i < $number; ++$i) {
            $newElement = $this->getSingleRandomElementFromProbaArray(
                array_diff_key($array, array_flip($randomElements))
            );

            if ($newElement !== null) {
                $randomElements[$i] = $newElement;
            }
        }

        return $randomElements;
    }

    public function getRandomDaedalusEquipmentFromProbaArray(array $array, int $number, Daedalus $daedalus): array
    {
        $equipmentNames = $this->getRandomElementsFromProbaArray($array, $number);

        $equipments = [];
        foreach ($equipmentNames as $equipmentName) {
            try {
                $equipment = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus)[0];
            } catch (\Exception $e) {
                continue;
            }
            $equipments[] = $equipment;
        }

        return $equipments;
    }

    /** Generate a random number from a Poisson process (Knuth algorithm).
     *
     * P(k) = exp(-lambda) * lambda^k / k!
     */
    public function poissonRandom(float $lambda): int
    {
        if ($lambda < 0) {
            throw new \Exception("poissonRandom: lambda ({$lambda}) must be positive");
        }

        $L = exp(-$lambda);
        $k = 0;
        $p = 1;

        do {
            ++$k;
            $p *= $this->randomPercent() / 100;
        } while ($p > $L);

        return $k - 1;
    }
}
