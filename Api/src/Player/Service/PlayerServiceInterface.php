<?php

namespace Mush\Player\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface PlayerServiceInterface
{
    public function persist(Player $player): Player;

    public function persistPlayerInfo(PlayerInfo $player): PlayerInfo;

    public function delete(Player $player): bool;

    public function findAll(): array;

    public function findById(int $id): ?Player;

    public function findOneByCharacter(string $character, Daedalus $daedalus): ?Player;

    public function changePlace(Player $player, Place $place): Player;

    public function createPlayer(Daedalus $daedalus, User $user, string $character): Player;

    public function endPlayer(Player $player, string $message, array $likedPlayers): Player;

    public function handleNewCycle(Player $player, \DateTime $date): Player;

    public function handleNewDay(Player $player, \DateTime $date): Player;

    public function findUserCurrentGame(User $user): ?Player;

    public function killPlayer(Player $player, string $endReason, \DateTime $time = new \DateTime(), ?Player $author = null): Player;
}
