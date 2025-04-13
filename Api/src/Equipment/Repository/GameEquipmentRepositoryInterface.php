<?php

declare(strict_types=1);

namespace Mush\Equipment\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

interface GameEquipmentRepositoryInterface
{
    public function findByDaedalus(Daedalus $daedalus): array;

    public function findById(int $id): ?GameEquipment;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): array;

    public function findEquipmentByNameAndDaedalus(string $name, Daedalus $daedalus): array;

    public function findByOwner(Player $player): array;

    public function findEquipmentByNameAndPlace(string $name, Place $place, int $quantity): array;

    public function findEquipmentByNameAndPlayer(string $name, Player $player, int $quantity): array;

    public function findByCriteria(GameEquipmentCriteria $criteria): array;

    public function save(GameEquipment $gameEquipment): void;

    public function delete(GameEquipment $gameEquipment): void;
}
