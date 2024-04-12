<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Mechanics\Ration;

#[ORM\Entity]
class ConsumableEffect
{
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $satiety = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    #[ORM\ManyToOne(targetEntity: Ration::class)]
    private Ration $ration;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $actionPoint = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $movementPoint = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $healthPoint = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $moralPoint = null;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $extraEffects = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): static
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getRation(): Ration
    {
        return $this->ration;
    }

    public function setRation(Ration $ration): static
    {
        $this->ration = $ration;

        return $this;
    }

    public function getActionPoint(): ?int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(?int $actionPoint): static
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }

    public function getMovementPoint(): ?int
    {
        return $this->movementPoint;
    }

    public function setMovementPoint(?int $movementPoint): static
    {
        $this->movementPoint = $movementPoint;

        return $this;
    }

    public function getHealthPoint(): ?int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(?int $healthPoint): static
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    public function getMoralPoint(): ?int
    {
        return $this->moralPoint;
    }

    public function setMoralPoint(?int $moralPoint): static
    {
        $this->moralPoint = $moralPoint;

        return $this;
    }

    public function getSatiety(): ?int
    {
        return $this->satiety;
    }

    public function setSatiety(?int $satiety): static
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function getExtraEffects(): array
    {
        return $this->extraEffects;
    }

    public function setExtraEffects(array $extraEffects): static
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }
}
