<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
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

    #[ORM\ManyToOne(targetEntity: AbstractModifierConfig::class)]
    private AbstractModifierConfig $modifierConfig;

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

    public function __construct(ModifierHolderInterface $holder, AbstractModifierConfig $modifierConfig)
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
        } else {
            throw new LogicException("this modifier don't have any valid holder");
        }

        $holder->addModifier($this);
    }

    public static function createNullEventModifier(): self
    {
        $modifier = new self(holder: Player::createNull(), modifierConfig: EventModifierConfig::createNull());
        $modifier->setId(0);

        return $modifier;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModifierConfig(): AbstractModifierConfig
    {
        return $this->modifierConfig;
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

    public function getCharge(): ?ChargeStatus
    {
        return $this->charge;
    }

    public function setCharge(ChargeStatus $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function isNull(): bool
    {
        return $this->getId() === 0 || $this->getModifierConfig()->isNull();
    }

    private function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
