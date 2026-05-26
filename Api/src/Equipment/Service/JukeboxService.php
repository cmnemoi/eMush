<?php

namespace Mush\Equipment\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JukeboxService
{
    public function __construct(private RandomServiceInterface $randomService) {}

    public function getSong(Daedalus $daedalus, GameEquipment $gameEquipment, int $offset = 0): Player
    {
        // obtain a seed by combining equipment id, daedalus id and the number of cycle elapsed. offset is used to simulate future / past songs
        $cyclesPerDay = $daedalus->getNumberOfCyclesPerDay();
        $cycles = $daedalus->getDay() * $cyclesPerDay + $daedalus->getCycle();
        $seed = $cycles + $daedalus->getId() + $gameEquipment->getId() + $offset;

        // select a player from the list of players on the daedalus
        $selection = $this->randomService->getPseudoRandomInt($seed, 0, $daedalus->getPlayers()->count() - 1);
        $selectedPlayer = $daedalus->getPlayers()->getSortedBy('createdAt')->get($selection);

        if ($selectedPlayer instanceof Player) {
            return $selectedPlayer;
        }

        throw new UnexpectedTypeException($selectedPlayer, Player::class);
    }
}
