<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

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
            throw new \Exception("equipment {$this->getName()} should have a holder");
        }
        if ($player === null) {
            return $place;
        }

        return $player;
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
}
