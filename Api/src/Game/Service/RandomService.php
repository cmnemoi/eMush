<?php

namespace Mush\Game\Service;

use Error;
use Mush\Action\Entity\Action;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Service\ModifierService;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class RandomService implements RandomServiceInterface
{

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
        return $this->getSuccessThreshold() <= $successRate;
    }

    public function getSuccessThreshold() : int {
        return $this->randomPercent();
    }

    public function outputCriticalChances(int $successRate, int $criticalFailRate = 0, int $criticalSuccessRate = 0): string
    {
        $chance = $this->randomPercent();

        if ($criticalFailRate > $successRate || 100 - $criticalSuccessRate < $successRate) {
            throw new Error('$criticalFailRate must be lower than $successRate and 100 - $criticalSuccessRate higher than $successRate');
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

        throw new Error('input percentages should range between 0 and 100');
    }

    public function getRandomPlayer(PlayerCollection $players): Player
    {
        if ($players->isEmpty()) {
            throw new Error('getRandomPlayer: collection is empty');
        }

        return current($this->getRandomElements($players->toArray()));
    }

    public function getRandomDisease(PlayerDiseaseCollection $collection): PlayerDisease
    {
        if ($collection->isEmpty()) {
            throw new Error('getRandomDisease: collection is empty');
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
            throw new Error('getItemInRoom: room has no items');
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
    public function getSingleRandomElementFromProbaArray(array $array): string
    {
        if (count($array) < 1) {
            throw new Error('getSingleRandomElement: array is not large enough');
        }

        // first create a cumulative form of the array
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

    /** Generate a random number from a Poisson process (Knuth algorithm).
     *
     * P(k) = exp(-lambda) * lambda^k / k!
     */
    public function poissonRandom(float $lambda): int
    {
        if ($lambda < 0) {
            throw new Error('poissonRandom: lambda must be positive');
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
