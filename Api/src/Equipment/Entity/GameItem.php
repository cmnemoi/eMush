<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Enum\EquipmentStatusEnum;

#[ORM\Entity]
class GameItem extends GameEquipment
{
    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'items')]
    private ?Player $player = null;

    public function __construct(EquipmentHolderInterface $equipmentHolder)
    {
        parent::__construct($equipmentHolder);

        if ($equipmentHolder instanceof Player) {
            $this->player = $equipmentHolder;
            $equipmentHolder->addEquipment($this);
        }
    }

    public function getHolder(): EquipmentHolderInterface
    {
        $player = $this->player;
        $place = $this->place;

        if ($player === null && $place === null) {
            throw new \RuntimeException("equipment {$this->getName()} should have a holder");
        }

        return $player ?? $place;
    }

    public function setHolder(EquipmentHolderInterface $holder): static
    {
        $oldHolder = $this->getHolder();

        if ($holder !== $oldHolder) {
            $oldHolder->removeEquipment($this);

            if ($holder instanceof Place) {
                $this->place = $holder;
                $this->player = null;
            } elseif ($holder instanceof Player) {
                $this->player = $holder;
                $this->place = null;
            }

            $holder->addEquipment($this);
        }

        return $this;
    }

    public function getPlace(): Place
    {
        $holder = $this->getHolder();

        if ($holder instanceof Place) {
            return $holder;
        }
        if ($holder instanceof Player) {
            return $holder->getPlace();
        }

        throw new \LogicException('Cannot find a holder');
    }

    public function shouldTriggerRoomTrap(): bool
    {
        return $this->getHolder() instanceof Place;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::ITEM;
    }

    public function canPlayerReach(Player $player): bool
    {
        return $this->getPlayer() === $player || $this->getHolder() === $player->getPlace();
    }

    public function getNormalizationType(): string
    {
        return LogParameterKeyEnum::ITEM . 's';
    }

    public function getPlantNameOrThrow(): string
    {
        /** @var Fruit $fruitMechanic */
        $fruitMechanic = $this->getMechanicByNameOrThrow(EquipmentMechanicEnum::FRUIT);

        return $fruitMechanic->getPlantName();
    }

    public function isPlantUnhealthy(): bool
    {
        return $this->hasStatus(EquipmentStatusEnum::PLANT_THIRSTY)
            || $this->hasStatus(EquipmentStatusEnum::PLANT_DRY)
            || $this->hasStatus(EquipmentStatusEnum::PLANT_DISEASED);
    }

    public function isCritical(): bool
    {
        $holder = $this->getHolder();
        if ($holder instanceof Player) {
            return EquipmentEnum::getCriticalItemsGivenPlayer($holder)->contains($this->getName());
        }

        throw new \LogicException('Cannot determine if item is critical if it is not held by a player');
    }
}
