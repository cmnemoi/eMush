<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

final class InMemoryGameEquipmentRepository implements GameEquipmentRepositoryInterface
{
    private array $gameEquipments = [];

    public function findByDaedalus(Daedalus $daedalus): array
    {
        /** @var Collection<int, Place> $places */
        $places = $daedalus->getPlaces();

        /** @var Collection<int, Player> $players */
        $players = $daedalus->getPlayers();

        return array_merge(
            $places->map(static fn (Place $place) => $place->getEquipments())->toArray(),
            $players->map(static fn (Player $player) => $player->getEquipments())->toArray()
        );
    }

    public function save(GameEquipment $gameEquipment): void
    {
        $this->gameEquipments[] = $gameEquipment;
    }
}
