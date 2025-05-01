<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class ItemConfig extends EquipmentConfig
{
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isStackable;

    public function createGameEquipment(
        EquipmentHolderInterface $holder,
    ): GameItem {
        $gameItem = new GameItem($holder);
        $gameItem
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this);

        return $gameItem;
    }

    public static function fromConfigData(array $configData): self
    {
        $config = new self();
        $config
            ->setIsStackable($configData['isStackable'])
            ->setName($configData['name'])
            ->setEquipmentName($configData['equipmentName'])
            ->setBreakableType($configData['breakableType'])
            ->setDismountedProducts($configData['dismountedProducts'])
            ->setIsPersonal($configData['isPersonal']);

        return $config;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    public function setIsStackable(bool $isStackable): static
    {
        $this->isStackable = $isStackable;

        return $this;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::ITEM;
    }
}
