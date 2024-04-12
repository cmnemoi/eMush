<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;

interface GameEquipmentServiceInterface
{
    public function persist(GameEquipment $equipment): GameEquipment;

    public function delete(GameEquipment $equipment): void;

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection;

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection;

    public function findById(int $id): ?GameEquipment;

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::PRIVATE,
        ?Player $author = null
    ): GameEquipment;

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN,
        ?Player $author = null
    ): GameEquipment;

    public function transformGameEquipmentToEquipmentWithName(
        string $resultName,
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
}
