<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

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
}
