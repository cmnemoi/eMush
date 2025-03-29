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

    public function findById(int $id): ?GameEquipment
    {
        return $this->gameEquipments[$id] ?? null;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): array
    {
        return array_filter($this->gameEquipments, static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name && $gameEquipment->getDaedalus()->equals($daedalus));
    }

    public function findEquipmentByNameAndDaedalus(string $name, Daedalus $daedalus): array
    {
        return array_filter($this->gameEquipments, static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name && $gameEquipment->getDaedalus()->equals($daedalus));
    }

    public function findByOwner(Player $player): array
    {
        return array_filter($this->gameEquipments, static fn (GameEquipment $gameEquipment) => $gameEquipment->getOwner()?->equals($player));
    }

    public function findEquipmentByNameAndPlace(string $name, Place $place, int $quantity): array
    {
        return array_filter($this->gameEquipments, static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name && $gameEquipment->getHolder() === $place);
    }

    public function findEquipmentByNameAndPlayer(string $name, Player $player, int $quantity): array
    {
        return array_filter($this->gameEquipments, static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name && $gameEquipment->getOwner()?->equals($player));
    }

    public function delete(GameEquipment $gameEquipment): void
    {
        unset($this->gameEquipments[$gameEquipment->getId()]);
    }

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
        $this->setupId($gameEquipment);
        $this->gameEquipments[$gameEquipment->getId()] = $gameEquipment;
    }

    private function setupId(GameEquipment $gameEquipment): void
    {
        $reflectionProperty = new \ReflectionProperty(GameEquipment::class, 'id');
        $reflectionProperty->setValue($gameEquipment, (int) serialize($gameEquipment));
    }
}
