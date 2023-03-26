<?php

namespace Mush\Hunter\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\ProbabilitiesCollection;

#[ORM\Entity]
class HunterWaveConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $waveConfigName;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $hunterPoolCosts = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $maxHunterPerWave = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private ProbabilitiesCollection $hunterDrawChances;

    public function __construct()
    {
        $this->hunterDrawChances = new ProbabilitiesCollection();
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

    public function getWaveConfigName(): string
    {
        return $this->waveConfigName;
    }

    public function setWaveConfigName(string $waveConfigName): static
    {
        $this->waveConfigName = $waveConfigName;

        return $this;
    }

    public function getHunterPoolCosts(): array
    {
        return $this->hunterPoolCosts;
    }

    public function setHunterPoolCosts(array $hunterPoolCosts): static
    {
        $this->hunterPoolCosts = $hunterPoolCosts;

        return $this;
    }

    public function getMaxHunterPerWave(): array
    {
        return $this->maxHunterPerWave;
    }

    public function setMaxHunterPerWave(array $maxHunterPerWave): static
    {
        $this->maxHunterPerWave = $maxHunterPerWave;

        return $this;
    }

    public function getHunterDrawChances(): ProbabilitiesCollection
    {
        return $this->hunterDrawChances;
    }

    public function setHunterDrawChances(ProbabilitiesCollection $hunterDrawChances): static
    {
        $this->hunterDrawChances = $hunterDrawChances;

        return $this;
    }

    public function getHunterDrawChance(string|int $key): ?int
    {
        return $this->hunterDrawChances->getItemProbability($key);
    }

    public function setHunterDrawChance(string|int $key, int $value): static
    {
        $this->hunterDrawChances->setItemProbability($key, $value);

        return $this;
    }
}
