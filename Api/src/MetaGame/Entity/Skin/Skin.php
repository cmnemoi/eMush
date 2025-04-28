<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\ORM\Mapping as ORM;

/**
 * An entity that stores a visual appearance for a SkinableEntityInterface (Player, Equipment, Place).
 */
#[ORM\Entity]
#[ORM\Table(name: 'skin')]
class Skin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name = 'default';

    #[ORM\ManyToOne(targetEntity: SkinSlotConfig::class)]
    private SkinSlotConfig $skinSlotConfig;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSkinSlotName(): string
    {
        return $this->skinSlotConfig->getName();
    }

    public function setSkinSlotConfig(SkinSlotConfig $skinSlotConfig): self
    {
        $this->skinSlotConfig = $skinSlotConfig;

        return $this;
    }

    public function getSkinSlotConfig(): SkinSlotConfig
    {
        return $this->skinSlotConfig;
    }
}
