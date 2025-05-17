<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Place\Entity\PlaceConfig;
use Mush\Player\Entity\Config\CharacterConfig;

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

    #[ORM\ManyToOne(targetEntity: CharacterConfig::class, inversedBy: 'skins')]
    private ?CharacterConfig $characterConfig;

    #[ORM\ManyToOne(targetEntity: EquipmentConfig::class, inversedBy: 'skins')]
    private ?EquipmentConfig $equipmentConfig;

    #[ORM\ManyToOne(targetEntity: PlaceConfig::class, inversedBy: 'skins')]
    private ?PlaceConfig $placeConfig;

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

    public function getSkinableEntity(): CharacterConfig|EquipmentConfig|PlaceConfig
    {
        $skinableEntity = $this->characterConfig ?? $this->equipmentConfig ?? $this->placeConfig;
        if ($skinableEntity === null) {
            throw new \RuntimeException('cannot find the skinable entity');
        }

        return $skinableEntity;
    }

    public function setSkinableEntity(CharacterConfig|EquipmentConfig|PlaceConfig $skinableEntity): self
    {
        $this->characterConfig = null;
        $this->equipmentConfig = null;

        if ($skinableEntity instanceof CharacterConfig) {
            $this->characterConfig = $skinableEntity;
        } elseif ($skinableEntity instanceof EquipmentConfig) {
            $this->equipmentConfig = $skinableEntity;
        } else {
            $this->placeConfig = $skinableEntity;
        }

        return $this;
    }
}
