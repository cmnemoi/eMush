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

    public function getHolder(): ?EquipmentHolderInterface
    {
        return $this->player ?: $this->place;
    }

    public function setHolder(?EquipmentHolderInterface $holder): static
    {


        if ($holder === null) {
            $this->place = null;
            $this->player = null;

            return $this;
        }

        if ($holder !== ($oldHolder = $this->getHolder())) {
            if ($oldHolder !== null) {
                $oldHolder->removeEquipment($this);
            }

            if ($holder instanceof Place) {
                $this->place = $holder;
            } elseif ($holder instanceof Player) {
                $this->player = $holder;
            }

            $holder->addEquipment($this);
        }

        return $this;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::ITEM;
    }
}
