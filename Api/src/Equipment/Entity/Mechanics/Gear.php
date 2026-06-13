<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['gear_read']],
    denormalizationContext: ['groups' => ['gear_write']],
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
class Gear extends EquipmentMechanic
{
    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    #[Groups(['gear_read', 'gear_write'])]
    private Collection $modifierConfigs;

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::GEAR;

        return $mechanics;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    /**
     * @param array<int, AbstractModifierConfig>|Collection<int, AbstractModifierConfig> $modifierConfigs
     */
    public function setModifierConfigs(array|Collection $modifierConfigs): static
    {
        if (\is_array($modifierConfigs)) {
            $modifierConfigs = new ArrayCollection($modifierConfigs);
        }

        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }

    public function hasModifierConfigByModifierName(string $modifierName): bool
    {
        return $this->modifierConfigs->filter(
            static fn (AbstractModifierConfig $modifierConfig) => $modifierConfig->getModifierName() === $modifierName
        )->count() > 0;
    }
}
