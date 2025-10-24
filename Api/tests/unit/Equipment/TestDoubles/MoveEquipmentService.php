<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\TestDoubles;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;

final class MoveEquipmentService implements GameEquipmentServiceInterface
{
    /**
     * @var array<GameEquipment>
     */
    private array $equipments = [];

    public function persist(GameEquipment $equipment): GameEquipment
    {
        $this->equipments[] = $equipment;

        return $equipment;
    }

    public function delete(GameEquipment $equipment): void
    {
        $this->equipments = array_filter($this->equipments, static fn (GameEquipment $item) => $item !== $equipment);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        $equipments = [];
        foreach ($this->equipments as $equipment) {
            if ($equipment->getName() === $name && $equipment->getPlace()->getDaedalus()->equals($daedalus)) {
                $equipments[] = $equipment;
            }
        }

        return new ArrayCollection($equipments);
    }

    public function findEquipmentsByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        return $this->findByNameAndDaedalus($name, $daedalus);
    }

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        return $this->findByNameAndDaedalus('', $daedalus);
    }

    public function findPatrolShipsByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection();
    }

    public function findById(int $id): ?GameEquipment
    {
        foreach ($this->equipments as $equipment) {
            if ($equipment->getId() === $id) {
                return $equipment;
            }
        }

        return null;
    }

    public function findGameEquipmentConfigFromNameAndDaedalus(string $equipmentName, Daedalus $daedalus): EquipmentConfig
    {
        return new EquipmentConfig();
    }

    public function createGameEquipmentFromName(string $equipmentName, EquipmentHolderInterface $equipmentHolder, array $reasons, \DateTime $time, string $visibility = VisibilityEnum::PRIVATE, ?Player $author = null): GameEquipment
    {
        return GameEquipmentFactory::createEquipmentByName($equipmentName, $equipmentHolder);
    }

    public function createGameEquipmentsFromName(string $equipmentName, EquipmentHolderInterface $equipmentHolder, int $quantity, array $reasons = [], \DateTime $time = new \DateTime(), string $visibility = VisibilityEnum::PRIVATE, ?Player $author = null): array
    {
        return [];
    }

    public function createGameEquipment(EquipmentConfig $equipmentConfig, EquipmentHolderInterface $holder, array $reasons, \DateTime $time, string $visibility = VisibilityEnum::HIDDEN, ?Player $author = null, ?string $patrolShipName = null): GameEquipment
    {
        return GameEquipmentFactory::createEquipmentByName($equipmentConfig->getName(), $holder);
    }

    public function transformGameEquipmentToEquipmentWithName(string $newEquipmentName, GameEquipment $input, EquipmentHolderInterface $holder, array $reasons, \DateTime $time, string $visibility = VisibilityEnum::HIDDEN): GameEquipment
    {
        return GameEquipmentFactory::createEquipmentByName($newEquipmentName, $holder);
    }

    public function transformGameEquipmentToEquipment(EquipmentConfig $resultConfig, GameEquipment $input, EquipmentHolderInterface $holder, array $reasons, \DateTime $time, string $visibility = VisibilityEnum::HIDDEN): GameEquipment
    {
        return GameEquipmentFactory::createEquipmentByName($resultConfig->getName(), $holder);
    }

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void {}

    public function handlePatrolShipDestruction(GameEquipment $patrolShip, ?Player $player, array $tags): void {}

    public function moveEquipmentTo(GameEquipment $equipment, EquipmentHolderInterface $newHolder, string $visibility = VisibilityEnum::HIDDEN, array $tags = [], \DateTime $time = new \DateTime(), ?Player $author = null): void
    {
        $equipment->setHolder($newHolder);
    }
}
