<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['item_config_read']],
    denormalizationContext: ['groups' => ['item_config_write']],
    paginationItemsPerPage: 25,
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
class ItemConfig extends EquipmentConfig
{
    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Groups(['item_config_read', 'item_config_write'])]
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

    #[Groups(['item_config_read'])]
    public function getId(): int
    {
        return parent::getId();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getName(): string
    {
        return parent::getName();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getEquipmentName(): string
    {
        return parent::getEquipmentName();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getMechanics(): Collection
    {
        return parent::getMechanics();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getBreakableType(): BreakableTypeEnum
    {
        return parent::getBreakableType();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getDismountedProducts(): array
    {
        return parent::getDismountedProducts();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getActionConfigs(): Collection
    {
        return parent::getActionConfigs();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getInitStatuses(): Collection
    {
        return parent::getInitStatuses();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getIsPersonal(): bool
    {
        return parent::getIsPersonal();
    }
}
