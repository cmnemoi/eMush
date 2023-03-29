<?php

namespace Mush\Hunter\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Enum\HunterTargetEnum;

#[ORM\Entity]
#[ORM\Table(name: 'hunter')]
class Hunter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: HunterConfig::class)]
    private HunterConfig $hunterConfig;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'hunters')]
    private Daedalus $daedalus;

    #[ORM\Column(type: 'integer')]
    private int $health;

    #[ORM\Column(type: 'integer')]
    private int $charge;

    #[ORM\Column(type: 'integer')]
    private int $armor;

    #[ORM\Column(type: 'string')]
    private string $target = HunterTargetEnum::DAEDALUS;

    #[ORM\Column(type: 'boolean')]
    private bool $inPool = true;

    public function __construct(HunterConfig $hunterConfig, Daedalus $daedalus)
    {
        $this->armor = $hunterConfig->getInitialArmor();
        $this->charge = $hunterConfig->getInitialCharge();
        $this->daedalus = $daedalus;
        $this->health = $hunterConfig->getInitialHealth();
        $this->hunterConfig = $hunterConfig;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHunterConfig(): HunterConfig
    {
        return $this->hunterConfig;
    }

    public function setHunterConfig(HunterConfig $hunterConfig): self
    {
        $this->hunterConfig = $hunterConfig;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function setHealth(int $health): self
    {
        $this->health = $health;

        return $this;
    }

    public function getCharge(): int
    {
        return $this->charge;
    }

    public function setCharge(int $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function setArmor(int $armor): self
    {
        $this->armor = $armor;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function isInPool(): bool
    {
        return $this->inPool;
    }

    public function putInPool(): self
    {
        $this->inPool = true;

        return $this;
    }

    public function unpool(): self
    {
        $this->inPool = false;

        return $this;
    }
}
