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
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['item_config_read']],
    denormalizationContext: ['groups' => ['item_config_write']],
    paginationItemsPerPage: 25,
    operations: [
        new GetCollection(filters: ['default.search_filter', 'default.order_filter']),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Get(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
    ],
)]
class DroneConfig extends NpcConfig
{
    public function createGameEquipment(EquipmentHolderInterface $holder): Drone
    {
        return (new Drone($holder))
            ->setName($this->getEquipmentShortName())
            ->setEquipment($this);
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DRONE;
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
    public function getMechanics(): Collection
    {
        return parent::getMechanics();
    }

    #[Groups(['item_config_read', 'item_config_write'])]
    public function getActionConfigs(): Collection
    {
        return parent::getActionConfigs();
    }
}
