<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;

#[ORM\Entity]
class RebelBaseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $key;

    #[ORM\Column(type: 'string', nullable: false, enumType: RebelBaseEnum::class, options: ['default' => RebelBaseEnum::NULL])]
    private RebelBaseEnum $name;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $contactOrder;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    public function __construct(string $key, RebelBaseEnum $name, int $contactOrder, ArrayCollection $modifierConfigs)
    {
        $this->key = $key;
        $this->name = $name;
        $this->contactOrder = $contactOrder;
        $this->modifierConfigs = $modifierConfigs;
    }

    public function getName(): RebelBaseEnum
    {
        return $this->name;
    }

    public function getContactOrder(): int
    {
        return $this->contactOrder;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    public function update(self $rebelBaseConfig): void
    {
        $this->key = $rebelBaseConfig->key;
        $this->name = $rebelBaseConfig->name;
        $this->contactOrder = $rebelBaseConfig->contactOrder;
        $this->modifierConfigs = $rebelBaseConfig->modifierConfigs;
    }
}
