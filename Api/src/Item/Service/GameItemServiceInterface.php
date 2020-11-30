<?php

namespace Mush\Item\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;

interface GameItemServiceInterface
{
    public function persist(GameItem $item): GameItem;

    public function delete(GameItem $item): void;

    public function findById(int $id): ?GameItem;

    public function createGameItemFromName(string $itemName, Daedalus $daedalus): GameItem;

    public function createGameItem(Item $item, Daedalus $daedalus): GameItem;

    public function canUseItemByName(string $itemName, Player $player, Daedalus $daedalus, string $reach): ArrayCollection;

    public function isOperational(GameItem $gameItem): bool;
}
