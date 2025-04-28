<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config that stores all the possible skins for a given skinnable entity.
 */
#[ORM\Entity]
#[ORM\Table(name: 'skin_slot_config')]
class SkinSlotConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: false, nullable: false)]
    private string $skinableClass;

    #[ORM\Column(type: 'string', length: 255, unique: false, nullable: false)]
    private string $skinableName;

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

    public function setSkinableClass(string $skinableClass): self
    {
        $this->skinableClass = $skinableClass;

        return $this;
    }

    public function getSkinableClass(): string
    {
        return $this->skinableClass;
    }

    public function setSkinableName(string $skinableName): self
    {
        $this->skinableName = $skinableName;

        return $this;
    }

    public function getSkinableName(): string
    {
        return $this->skinableName;
    }
}
