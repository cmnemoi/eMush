<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionTargetName;
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

    public function isInShelf(): bool
    {
        return $this->getHolder() instanceof Place;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::ITEM;
    }

    public function getActionTargetName(array $context): string
    {
        if (\array_key_exists(ActionTargetName::TERMINAL->value, $context) && $context[ActionTargetName::TERMINAL->value] === $this) {
            return ActionTargetName::TERMINAL->value;
        }

        return ActionTargetName::ITEM->value;
    }
}
