<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Exception\LogicException;

#[ORM\Entity]
#[ORM\Table(name: 'modifier')]
class Modifier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ModifierConfig::class)]
    private ModifierConfig $config;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $equipment = null;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private ?Daedalus $daedalus = null;

    public function __construct(ModifierHolder $holder, ModifierConfig $config)
    {
        $this->setModifierHolder($holder);
        $this->config = $config;

        $holder->addModifier($this);
    }

    public function getConfig(): ModifierConfig
    {
        return $this->config;
    }

    private function setModifierHolder(ModifierHolder $holder): void
    {
        if ($holder instanceof Player) {
            $this->player = $holder;
        } elseif ($holder instanceof Place) {
            $this->place = $holder;
        } elseif ($holder instanceof Daedalus) {
            $this->daedalus = $holder;
        } elseif ($holder instanceof GameEquipment) {
            $this->equipment = $holder;
        }
    }

    public function getModifierHolder(): ModifierHolder
    {
        if ($this->player) {
            return $this->player;
        } elseif ($this->place) {
            return $this->place;
        } elseif ($this->daedalus) {
            return $this->daedalus;
        } elseif ($this->equipment) {
            return $this->equipment;
        } else {
            throw new LogicException("This modifier don't have any valid holder");
        }
    }

    public function getId(): int
    {
        return $this->id;
    }
}
