<?php

namespace Mush\Player\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;

interface PlayerServiceInterface
{
    public function persist(Player $player): Player;

    public function findById(int $id): ?Player;

    public function findOneByCharacter(string $character, ?Daedalus $daedalus = null): ?Player;

    public function createPlayer(Daedalus $daedalus, string $character): Player;

    public function handleNewCycle(Player $player, \DateTime $date): Player;

    public function handleNewDay(Player $player, \DateTime $date): Player;

    public function findUserCurrentGame(User $user): ?Player;

    public function handlePlayerModifier(Player $player, ActionModifier $actionModifier, \DateTime $date = null): Player;
}
