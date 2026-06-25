<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(filters: ['default.search_filter', 'default.order_filter']),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Get(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['fruit_read']],
    denormalizationContext: ['groups' => ['fruit_write']],
    paginationItemsPerPage: 25,
)]
class Fruit extends Ration
{
    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['fruit_read', 'fruit_write'])]
    private string $plantName;

    #[Groups(['fruit_read', 'fruit_write'])]
    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::FRUIT;

        return $mechanics;
    }

    public function getPlantName(): string
    {
        return $this->plantName;
    }

    public function setPlantName(string $plantName): self
    {
        $this->plantName = $plantName;

        return $this;
    }

    #[Groups(['fruit_read'])]
    public function getId(): int
    {
        return parent::getId();
    }

    #[Groups(['fruit_read', 'fruit_write'])]
    public function getName(): string
    {
        return parent::getName();
    }

    #[Groups(['fruit_read', 'fruit_write'])]
    public function getActions(): Collection
    {
        return parent::getActions();
    }
}
