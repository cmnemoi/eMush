<?php

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

interface RandomServiceInterface
{
    public function random(int $min, int $max): int;

    public function randomPercent(): int;

    public function isSuccessful(int $successRate): bool;

    public function getRandomPlayer(PlayerCollection $players): Player;

    public function getPlayerInRoom(Place $place): Player;

    public function getAlivePlayerInDaedalus(Daedalus $ship): Player;

    public function getItemInRoom(Place $place): GameItem;

    public function getRandomElements(array $array, int $number = 1): array;

    public function getSingleRandomElementFromProbaArray(array $array): string;

    public function getRandomElementsFromProbaArray(array $array, int $number): array;
}
