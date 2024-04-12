<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Place\Entity\PlaceConfig;

#[ORM\Entity]
#[ORM\Table(name: 'config_daedalus')]
class DaedalusConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initOxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initHull = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $initShield = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initHunterPoints = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initCombustionChamberFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxOxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxHull = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxShield = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $maxCombustionChamberFuel = 0;

    #[ORM\OneToOne(targetEntity: RandomItemPlaces::class, cascade: ['ALL'])]
    private ?RandomItemPlaces $randomItemPlaces = null;

    #[ORM\ManyToMany(targetEntity: PlaceConfig::class)]
    private Collection $placeConfigs;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $dailySporeNb = 4;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $nbMush = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cyclePerGameDay = 8;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycleLength = 0; // in minutes

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

    public function getInitOxygen(): int
    {
        return $this->initOxygen;
    }

    public function setInitOxygen(int $initOxygen): static
    {
        $this->initOxygen = $initOxygen;

        return $this;
    }

    public function getInitFuel(): int
    {
        return $this->initFuel;
    }

    public function setInitFuel(int $initFuel): static
    {
        $this->initFuel = $initFuel;

        return $this;
    }

    public function getInitHull(): int
    {
        return $this->initHull;
    }

    public function setInitHull(int $initHull): static
    {
        $this->initHull = $initHull;

        return $this;
    }

    public function getInitShield(): int
    {
        return $this->initShield;
    }

    public function setInitShield(int $initShield): static
    {
        $this->initShield = $initShield;

        return $this;
    }

    public function getInitHunterPoints(): int
    {
        return $this->initHunterPoints;
    }

    public function setInitHunterPoints(int $initHunterPoints): static
    {
        $this->initHunterPoints = $initHunterPoints;

        return $this;
    }

    public function getInitCombustionChamberFuel(): int
    {
        return $this->initCombustionChamberFuel;
    }

    public function setInitCombustionChamberFuel(int $initCombustionChamberFuel): static
    {
        $this->initCombustionChamberFuel = $initCombustionChamberFuel;

        return $this;
    }

    public function getRandomItemPlaces(): ?RandomItemPlaces
    {
        return $this->randomItemPlaces;
    }

    public function setRandomItemPlaces(RandomItemPlaces $randomItemPlaces): static
    {
        $this->randomItemPlaces = $randomItemPlaces;

        return $this;
    }

    public function getPlaceConfigs(): Collection
    {
        return $this->placeConfigs;
    }

    /**
     * @param array<array-key, PlaceConfig>|Collection<array-key, PlaceConfig> $placeConfigs
     */
    public function setPlaceConfigs(array|Collection $placeConfigs): static
    {
        if (\is_array($placeConfigs)) {
            $placeConfigs = new ArrayCollection($placeConfigs);
        }

        $this->placeConfigs = $placeConfigs;

        return $this;
    }

    public function getDailySporeNb(): int
    {
        return $this->dailySporeNb;
    }

    public function setDailySporeNb(int $dailySporeNb): static
    {
        $this->dailySporeNb = $dailySporeNb;

        return $this;
    }

    public function getMaxOxygen(): int
    {
        return $this->maxOxygen;
    }

    public function setMaxOxygen(int $maxOxygen): static
    {
        $this->maxOxygen = $maxOxygen;

        return $this;
    }

    public function getMaxFuel(): int
    {
        return $this->maxFuel;
    }

    public function setMaxFuel(int $maxFuel): static
    {
        $this->maxFuel = $maxFuel;

        return $this;
    }

    public function getMaxHull(): int
    {
        return $this->maxHull;
    }

    public function setMaxHull(int $maxHull): static
    {
        $this->maxHull = $maxHull;

        return $this;
    }

    public function getMaxShield(): int
    {
        return $this->maxShield;
    }

    public function setMaxShield(int $maxShield): static
    {
        $this->maxShield = $maxShield;

        return $this;
    }

    public function getMaxCombustionChamberFuel(): int
    {
        return $this->maxCombustionChamberFuel;
    }

    public function setMaxCombustionChamberFuel(int $maxCombustionChamberFuel): static
    {
        $this->maxCombustionChamberFuel = $maxCombustionChamberFuel;

        return $this;
    }

    public function getVariableFromName(string $variableName): int
    {
        switch ($variableName) {
            case DaedalusVariableEnum::OXYGEN:
                return $this->maxOxygen;

            case DaedalusVariableEnum::FUEL:
                return $this->maxFuel;

            case DaedalusVariableEnum::HULL:
                return $this->maxHull;

            case DaedalusVariableEnum::SHIELD:
                return $this->maxShield;

            case DaedalusVariableEnum::HUNTER_POINTS:
                return $this->initHunterPoints;

            case DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL:
                return $this->maxCombustionChamberFuel;

            default:
                throw new \LogicException('this is not a valid daedalusVariable');
        }
    }

    public function getNbMush(): int
    {
        return $this->nbMush;
    }

    public function setNbMush(int $nbMush): static
    {
        $this->nbMush = $nbMush;

        return $this;
    }

    public function getCyclePerGameDay(): int
    {
        return $this->cyclePerGameDay;
    }

    public function setCyclePerGameDay(int $cyclePerGameDay): static
    {
        $this->cyclePerGameDay = $cyclePerGameDay;

        return $this;
    }

    public function getCycleLength(): int
    {
        return $this->cycleLength;
    }

    public function setCycleLength(int $cycleLength): static
    {
        $this->cycleLength = $cycleLength;

        return $this;
    }
}
