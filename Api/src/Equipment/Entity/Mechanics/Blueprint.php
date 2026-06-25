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
    normalizationContext: ['groups' => ['blueprint_read']],
    denormalizationContext: ['groups' => ['blueprint_write']],
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
class Blueprint extends Tool
{
    #[ORM\Column(type: 'string', unique: false, nullable: false)]
    #[Groups(['blueprint_read', 'blueprint_write'])]
    private string $craftedEquipmentName;

    /**
     * @var array<string, int>
     */
    #[ORM\Column(type: 'array', nullable: false)]
    #[Groups(['blueprint_read', 'blueprint_write'])]
    private array $ingredients = [];

    #[Groups(['blueprint_read', 'blueprint_write'])]
    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::BLUEPRINT;

        return $mechanics;
    }

    public function getCraftedEquipmentName(): string
    {
        return $this->craftedEquipmentName;
    }

    public function setCraftedEquipmentName(string $craftedEquipmentName): self
    {
        $this->craftedEquipmentName = $craftedEquipmentName;

        return $this;
    }

    public function getIngredientsNames(): string
    {
        return implode('+', array_keys($this->ingredients));
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }

    #[Groups(['blueprint_read'])]
    public function getId(): int
    {
        return parent::getId();
    }

    #[Groups(['blueprint_read', 'blueprint_write'])]
    public function getName(): string
    {
        return parent::getName();
    }

    #[Groups(['blueprint_read', 'blueprint_write'])]
    public function getActions(): Collection
    {
        return parent::getActions();
    }
}
