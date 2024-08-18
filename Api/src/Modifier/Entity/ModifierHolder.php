<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Exception\LogicException;

#[ORM\Entity]
#[ORM\Table(name: 'modifier_holder')]
class ModifierHolder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(mappedBy: 'modifierHolder', targetEntity: GameModifier::class, cascade: ['REMOVE'])]
    private GameModifier $gameModifier;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $gameEquipment = null;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private ?Daedalus $daedalus = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameModifier(): GameModifier
    {
        return $this->gameModifier;
    }

    public function setGameModifier(GameModifier $gameModifier): static
    {
        $this->gameModifier = $gameModifier;

        return $this;
    }

    public function getModifierHolder(): ModifierHolderInterface
    {
        if ($this->player) {
            return $this->player;
        }
        if ($this->place) {
            return $this->place;
        }
        if ($this->daedalus) {
            return $this->daedalus;
        }
        if ($this->gameEquipment) {
            return $this->gameEquipment;
        }

        throw new LogicException("this modifier don't have any valid holder");
    }

    public function setModifierHolder(ModifierHolderInterface $modifierHolder): static
    {
        if ($modifierHolder instanceof Player) {
            $this->player = $modifierHolder;

            return $this;
        }
        if ($modifierHolder instanceof GameEquipment) {
            $this->gameEquipment = $modifierHolder;

            return $this;
        }
        if ($modifierHolder instanceof Daedalus) {
            $this->daedalus = $modifierHolder;

            return $this;
        }
        if ($modifierHolder instanceof Place) {
            $this->place = $modifierHolder;

            return $this;
        }

        throw new LogicException("this modifierHolder don't have any valid holder");
    }
}
