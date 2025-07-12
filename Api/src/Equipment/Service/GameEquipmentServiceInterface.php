<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;

/**
 * GameEquipment Service allow us to fetch "RoomEquipment" or GameItem.
 */
interface GameEquipmentServiceInterface
{
    public function persist(GameEquipment $equipment): GameEquipment;

    /**
     * @deprecated DO NOT USE THIS METHOD !!! Use DeleteEquipmentService instead !!!
     */
    public function delete(GameEquipment $equipment): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection;

    public function findEquipmentsByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection;

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection;

    public function findPatrolShipsByDaedalus(Daedalus $daedalus): ArrayCollection;

    public function findById(int $id): ?GameEquipment;

    public function findGameEquipmentConfigFromNameAndDaedalus(
        string $equipmentName,
        Daedalus $daedalus
    ): EquipmentConfig;

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::PRIVATE,
        ?Player $author = null,
    ): GameEquipment;

    /**
     * @return array<GameEquipment>
     */
    public function createGameEquipmentsFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        int $quantity,
        array $reasons = [],
        \DateTime $time = new \DateTime(),
        string $visibility = VisibilityEnum::PRIVATE,
        ?Player $author = null
    ): array;

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN,
        ?Player $author = null,
        ?string $patrolShipName = null
    ): GameEquipment;

    public function transformGameEquipmentToEquipmentWithName(
        string $newEquipmentName,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment;

    public function transformGameEquipmentToEquipment(
        EquipmentConfig $resultConfig,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment;

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void;

    /**
     * Handle patrol ship destruction.
     *
     * - destroy patrol ship
     * - move patrol ship content to space
     *
     * @param GameEquipment $patrolShip patrol ship to be destroyed
     * @param ?Player       $player     player inside the patrol ship
     * @param array         $tags       tags of the event leading to the destruction
     */
    public function handlePatrolShipDestruction(GameEquipment $patrolShip, ?Player $player, array $tags): void;

    public function moveEquipmentTo(
        GameEquipment $equipment,
        EquipmentHolderInterface $newHolder,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        ?Player $author = null
    ): void;
}
