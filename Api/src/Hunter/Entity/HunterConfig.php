<?php

namespace Mush\Hunter\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\ProbaCollection;

class HunterConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $hunterName;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $initialHealth;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $initialCharge;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $initialArmor;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $minDamage;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $maxDamage;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $hitChance;

    #[ORM\Column(type: 'int', length: 255, nullable: false)]
    private int $dodgeChance;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getHunterName(): string
    {
        return $this->hunterName;
    }

    public function setHunterName(string $hunterName): static
    {
        $this->hunterName = $hunterName;

        return $this;
    }

    public function getInitialHealth(): int
    {
        return $this->initialHealth;
    }

    public function setInitialHealth(int $initialHealth): static
    {
        $this->initialHealth = $initialHealth;

        return $this;
    }

    public function getInitialCharge(): int
    {
        return $this->initialCharge;
    }

    public function setInitialCharge(int $initialCharge): static
    {
        $this->initialCharge = $initialCharge;

        return $this;
    }

    public function getInitialArmor(): int
    {
        return $this->initialArmor;
    }

    public function setInitialArmor(int $initialArmor): static
    {
        $this->initialArmor = $initialArmor;

        return $this;
    }

    public function getDamageRange(): ProbaCollection
    {
        return new ProbaCollection($this->damageRange);
    }

    public function setDamageRange(ProbaCollection $damageRange): static
    {
        $this->damageRange = $damageRange->toArray();

        return $this;
    }

    public function getHitChance(): int
    {
        return $this->hitChance;
    }

    public function setHitChance(int $hitChance): static
    {
        $this->hitChance = $hitChance;

        return $this;
    }

    public function getDodgeChance(): int
    {
        return $this->dodgeChance;
    }

    public function setDodgeChance(int $dodgeChance): static
    {
        $this->dodgeChance = $dodgeChance;

        return $this;
    }

}