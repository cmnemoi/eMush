<?php

declare(strict_types=1);

namespace Mush\Hunter\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class HunterTarget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private ?Daedalus $daedalus = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $patrolShip = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $player = null;

    public function __construct(Hunter $hunter)
    {
        $this->setTargetEntity($hunter->getDaedalus());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTargetEntity(): HunterTargetEntityInterface
    {
        $target = $this->daedalus ?? $this->patrolShip ?? $this->player;

        if ($target === null) {
            throw new \Exception('Hunter target type should be a Daedalus, GameEquipment or Player instance, got null.');
        }

        return $target;
    }

    public function setTargetEntity(HunterTargetEntityInterface $target): self
    {
        $this->daedalus = null;
        $this->patrolShip = null;
        $this->player = null;

        switch (true) {
            case $target instanceof Daedalus:
                $this->daedalus = $target;
                break;
            case $target instanceof GameEquipment:
                $this->patrolShip = $target;
                break;
            case $target instanceof Player:
                $this->player = $target;
                break;
            default:
                throw new \Exception('Hunter target type should be a Daedalus, GameEquipment or Player instance, got a ' . get_class($target) . '.');
        }

        return $this;
    }

    public function getType(): string
    {
        if ($this->daedalus !== null) {
            return HunterTargetEnum::DAEDALUS;
        }

        if ($this->patrolShip !== null) {
            return HunterTargetEnum::PATROL_SHIP;
        }

        if ($this->player !== null) {
            return HunterTargetEnum::PLAYER;
        }

        throw new \Exception('Hunter target type should be a Daedalus, GameEquipment or Player instance, got null.');
    }

    public function isInBattle(): bool
    {
        return $this->getTargetEntity()->isInSpaceBattle();
    }

    public function reset(): void
    {
        $this->daedalus = null;
        $this->patrolShip = null;
        $this->player = null;
    }
}
