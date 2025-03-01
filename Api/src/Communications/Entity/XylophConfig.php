<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Enum\XylophEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;

#[ORM\Entity]
class XylophConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $key;

    #[ORM\Column(type: 'string', nullable: false, enumType: XylophEnum::class, options: ['default' => XylophEnum::NULL])]
    private XylophEnum $name;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => '0'])]
    private int $weight;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => '-1'])]
    private int $quantity;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    public function __construct(string $key, XylophEnum $name, int $weight, int $quantity, ArrayCollection $modifierConfigs)
    {
        $this->key = $key;
        $this->name = $name;
        $this->weight = $weight;
        $this->quantity = $quantity;
        $this->modifierConfigs = $modifierConfigs;
    }

    public function getName(): XylophEnum
    {
        return $this->name;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $quantity): void
    {
        $this->weight = $quantity;
    }

    public function getQuantity(): int
    {
        $result = $this->quantity;

        if ($result < 0) {
            throw new \LogicException('called xyloph modifier with negative quantity');
        }

        return $result;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    public function update(self $xylophConfig): void
    {
        $this->key = $xylophConfig->key;
        $this->name = $xylophConfig->name;
        $this->weight = $xylophConfig->weight;
        $this->modifierConfigs = $xylophConfig->modifierConfigs;
    }
}
