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
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $hunterName;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initialHealth;

    #[ORM\ManyToMany(targetEntity: StatusConfig::class)]
    private Collection $initialStatuses;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $damageRange = [];

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
    private int $spawnDifficulty;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $scrapDropTable = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => '[]'])]
    private array $numberOfDroppedScrap = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $targetProbabilities = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => '0'])]
    private int $bonusAfterFailedShot = 0;

    public function __construct()
    {
        $this->initialStatuses = new ArrayCollection();
    }

    public function getId(): int
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

    public function setDamageRange(array|ProbaCollection $damageRange): static
    {
        if ($damageRange instanceof ProbaCollection) {
            $damageRange = $damageRange->toArray();
        }

        $this->damageRange = $damageRange;

        return $this;
    }

    public function getHitChance(): int
    {
        return $this->hitChance;
    }

    public function setHitChance(int $hitChance): static
    {
        if ($hitChance < 0) {
            $hitChance = 0;
        }
        if ($hitChance > 100) {
            $hitChance = 100;
        }

        $this->hitChance = $hitChance;

        return $this;
    }

    public function getDodgeChance(): int
    {
        if ($this->dodgeChance < 0) {
            $this->dodgeChance = 0;
        }
        if ($this->dodgeChance > 100) {
            $this->dodgeChance = 100;
        }

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

    public function getScrapDropTable(): ProbaCollection
    {
        return new ProbaCollection($this->scrapDropTable);
    }

    public function setScrapDropTable(array|ProbaCollection $scrapDropTable): static
    {
        if ($scrapDropTable instanceof ProbaCollection) {
            $scrapDropTable = $scrapDropTable->toArray();
        }

        $this->scrapDropTable = $scrapDropTable;

        return $this;
    }

    public function getNumberOfDroppedScrap(): ProbaCollection
    {
        return new ProbaCollection($this->numberOfDroppedScrap);
    }

    public function setNumberOfDroppedScrap(array|ProbaCollection $numberOfDroppedScrap): static
    {
        if ($numberOfDroppedScrap instanceof ProbaCollection) {
            $numberOfDroppedScrap = $numberOfDroppedScrap->toArray();
        }

        $this->numberOfDroppedScrap = $numberOfDroppedScrap;

        return $this;
    }

    public function getTargetProbabilities(): ?ProbaCollection
    {
        if ($this->targetProbabilities === null) {
            return null;
        }

        return new ProbaCollection($this->targetProbabilities);
    }

    public function setTargetProbabilities(array|ProbaCollection $targetProbabilities): static
    {
        if ($targetProbabilities instanceof ProbaCollection) {
            $targetProbabilities = $targetProbabilities->toArray();
        }

        $this->targetProbabilities = $targetProbabilities;

        return $this;
    }

    public function addTargetProbability(string $target, int $probability): static
    {
        if ($this->targetProbabilities === null) {
            $this->targetProbabilities = [];
        }
        $this->targetProbabilities[$target] = $probability;

        return $this;
    }

    public function getBonusAfterFailedShot(): int
    {
        return $this->bonusAfterFailedShot;
    }

    public function setBonusAfterFailedShot(int $bonusAfterFailedShot): static
    {
        $this->bonusAfterFailedShot = $bonusAfterFailedShot;

        return $this;
    }
}
