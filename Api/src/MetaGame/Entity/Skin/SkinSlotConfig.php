<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToMany( targetEntity: Skin::class)]
    private Collection $skins;

    public function __construct()
    {
        $this->skins = new ArrayCollection();
    }

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

    public function getSkins(): ArrayCollection
    {
        return new ArrayCollection($this->skins->toArray());
    }

    public function setSkins(ArrayCollection $skins): self
    {
        $this->skins = $skins;

        return $this;
    }

    public function addSkin(Skin $skin): self
    {
        $this->skins->add($skin);

        return $this;
    }
}
