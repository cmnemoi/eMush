<?php


namespace Mush\Item\Service;


use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameFruit;

interface GameFruitServiceInterface
{
    public function persist(GameFruit $gameFruit): GameFruit;

    public function createFruit(Daedalus $daedalus): GameFruit;

    public function createBanana(Daedalus $daedalus): GameFruit;
}