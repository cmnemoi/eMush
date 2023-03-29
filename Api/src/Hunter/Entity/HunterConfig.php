<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\ProbaCollection;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\Table(name: 'hunter_config')]
class HunterConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $hunterName;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initialHealth;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initialStatuses;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $damageRange;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $hitChance;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dodgeChance;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $drawCost;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxPerWave;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $drawWeight;

    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private string $spawnDifficulty;

    public function __construct()
    {
        $this->initialStatuses = new ArrayCollection();
    }

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

    public function getInitialStatuses(): Collection
    {
        return $this->initialStatuses;
    }

    /**
     * @param Collection<int, StatusConfig> $initialStatuses
     */
    public function setInitialStatuses(Collection|array $initialStatuses): static
    {
        if (is_array($initialStatuses)) {
            $initialStatuses = new ArrayCollection($initialStatuses);
        }

        $this->initialStatuses = $initialStatuses;

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

    public function getDrawCost(): int
    {
        return $this->drawCost;
    }

    public function setDrawCost(int $drawCost): static
    {
        $this->drawCost = $drawCost;

        return $this;
    }

    public function getMaxPerWave(): ?int
    {
        return $this->maxPerWave;
    }

    public function setMaxPerWave(?int $maxPerWave): static
    {
        $this->maxPerWave = $maxPerWave;

        return $this;
    }

    public function getDrawWeight(): int
    {
        return $this->drawWeight;
    }

    public function setDrawWeight(int $drawWeight): static
    {
        $this->drawWeight = $drawWeight;

        return $this;
    }

    public function getSpawnDifficulty(): int
    {
        return $this->spawnDifficulty;
    }

    public function setSpawnDifficulty(int $spawnDifficulty): static
    {
        $this->spawnDifficulty = $spawnDifficulty;

        return $this;
    }
}
