<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'skin_slot')]
class SkinSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Skin::class)]
    private Skin $skin;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setNameFromConfig(SkinSlotConfig $skinSlotConfig): self
    {
        $this->name = $skinSlotConfig->getName();

        return $this;
    }

    public function getSkin(): Skin
    {
        return $this->skin;
    }

    public function setSkin(Skin $skin): self
    {
        $this->skin = $skin;

        return $this;
    }
}
