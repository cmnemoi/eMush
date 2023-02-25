<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Symfony\Component\Validator\Exception\LogicException;

#[ORM\Entity]
#[ORM\Table(name: 'game_modifier')]
class GameModifier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ModifierConfig::class)]
    private ModifierConfig $modifierConfig;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $gameEquipment = null;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private ?Daedalus $daedalus = null;

    #[ORM\ManyToOne(targetEntity: ChargeStatus::class)]
    private ?ChargeStatus $charge = null;

    public function __construct(ModifierHolder $holder, ModifierConfig $modifierConfig)
    {
        $this->modifierConfig = $modifierConfig;

        if ($holder instanceof Player) {
            $this->player = $holder;
        } elseif ($holder instanceof Place) {
            $this->place = $holder;
        } elseif ($holder instanceof Daedalus) {
            $this->daedalus = $holder;
        } elseif ($holder instanceof GameEquipment) {
            $this->gameEquipment = $holder;
        }

        $holder->addModifier($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModifierConfig(): ModifierConfig
    {
        return $this->modifierConfig;
    }

    public function getModifierHolder(): ModifierHolder
    {
        if ($this->player) {
            return $this->player;
        } elseif ($this->place) {
            return $this->place;
        } elseif ($this->daedalus) {
            return $this->daedalus;
        } elseif ($this->gameEquipment) {
            return $this->gameEquipment;
        } else {
            throw new LogicException("this modifier don't have any valid holder");
        }
    }

    public function getCharge(): ?ChargeStatus
    {
        return $this->charge;
    }

    public function setCharge(ChargeStatus $charge): self
    {
        $this->charge = $charge;

        return $this;
    }
}
