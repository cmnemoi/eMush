<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

/**
 * A skinnable entity can have several skins on different slots,
 * e.g. player can have an emoticon and a costume OR place can have several modifications.
 */
#[ORM\Entity]
#[ORM\Table(name: 'skin_slot')]
class SkinSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: false, nullable: false)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Skin::class)]
    private ?Skin $skin;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'skinSlots')]
    private ?Place $place;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'skinSlots')]
    private ?Player $player;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class, inversedBy: 'skinSlots')]
    private ?GameEquipment $gameEquipment;

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

    public function getSkin(): ?Skin
    {
        return $this->skin;
    }

    public function setSkin(Skin $skin): self
    {
        $this->skin = $skin;

        return $this;
    }

    public function getSkinableEntity(): SkinableEntityInterface
    {
        $skinableEntity = $this->player ?? $this->place ?? $this->gameEquipment;
        if ($skinableEntity === null) {
            throw new \RuntimeException('cannot find the skinable entity');
        }

        return $skinableEntity;
    }

    public function setSkinableEntity(SkinableEntityInterface $skinableEntity): self
    {
        $this->place = null;
        $this->player = null;
        $this->gameEquipment = null;

        if ($skinableEntity instanceof Place) {
            $this->place = $skinableEntity;
        } elseif ($skinableEntity instanceof Player) {
            $this->player = $skinableEntity;
        } elseif ($skinableEntity instanceof GameEquipment) {
            $this->gameEquipment = $skinableEntity;
        }

        return $this;
    }
}
