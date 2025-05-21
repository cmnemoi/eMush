<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\CharacterSetEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\HolidayEnum;
use Mush\Place\Entity\PlaceConfig;

#[ORM\Entity]
#[ORM\Table(name: 'config_daedalus')]
class DaedalusConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false, options: ['default' => ''])]
    private string $name;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initOxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initHull = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initShield = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initHunterPoints = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $initCombustionChamberFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $maxOxygen = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $maxFuel = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $maxHull = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $maxShield = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $maxCombustionChamberFuel = 0;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $startingApprentrons = [];

    #[ORM\OneToOne(targetEntity: RandomItemPlaces::class, cascade: ['ALL'])]
    private ?RandomItemPlaces $randomItemPlaces = null;

    #[ORM\ManyToMany(targetEntity: PlaceConfig::class)]
    private Collection $placeConfigs;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $dailySporeNb = 4;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $nbMush = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $cyclePerGameDay = 8;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $cycleLength = 0; // in minutes

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $numberOfProjectsByBatch = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $humanSkillSlots = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $mushSkillSlots = 0;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => 'none'])]
    private string $holiday = 'none';

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $freeLove = false;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $numberOfCyclesBeforeNextRebelBaseContact = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $rebelBaseContactDurationMin = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $rebelBaseContactDurationMax = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $startingRandomBlueprintCount = 0;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $randomBlueprints = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 16])]
    private int $playerCount = 16;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => CharacterSetEnum::FINOLA_CHAO])]
    private string $chaolaToggle = CharacterSetEnum::FINOLA_CHAO;

    public static function fromConfigData(array $configData): self
    {
        $daedalusConfig = new self();
        $daedalusConfig
            ->setName($configData['name'])
            ->setInitOxygen($configData['initOxygen'])
            ->setInitFuel($configData['initFuel'])
            ->setInitHull($configData['initHull'])
            ->setInitShield($configData['initShield'])
            ->setInitHunterPoints($configData['initHunterPoints'])
            ->setInitCombustionChamberFuel($configData['initCombustionChamberFuel'])
            ->setStartingApprentrons($configData['startingApprentrons'])
            ->setMaxOxygen($configData['maxOxygen'])
            ->setMaxFuel($configData['maxFuel'])
            ->setMaxHull($configData['maxHull'])
            ->setMaxShield($configData['maxShield'])
            ->setMaxCombustionChamberFuel($configData['maxCombustionChamberFuel'])
            ->setDailySporeNb($configData['dailySporeNb'])
            ->setNbMush($configData['nbMush'])
            ->setCyclePerGameDay($configData['cyclePerGameDay'])
            ->setCycleLength($configData['cycleLength'])
            ->setNumberOfProjectsByBatch($configData['numberOfProjectsByBatch'])
            ->setHumanSkillSlots($configData['humanSkillSlots'])
            ->setMushSkillSlots($configData['mushSkillSlots'])
            ->setHoliday($configData['applyHoliday'])
            ->setNumberOfCyclesBeforeNextRebelBaseContact($configData['numberOfCyclesBeforeNextRebelBaseContact'])
            ->setRebelBaseContactDurationMin($configData['rebelBaseContactDurationMin'])
            ->setRebelBaseContactDurationMax($configData['rebelBaseContactDurationMax'])
            ->setStartingRandomBlueprintCount($configData['startingRandomBlueprintCount'])
            ->setRandomBlueprints($configData['randomBlueprints']);

        return $daedalusConfig;
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

    public function getStartingApprentrons(): ProbaCollection
    {
        return new ProbaCollection($this->startingApprentrons);
    }

    public function setStartingApprentrons(array $startingApprentrons): static
    {
        $this->startingApprentrons = $startingApprentrons;

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
        return match ($variableName) {
            DaedalusVariableEnum::OXYGEN => $this->maxOxygen,
            DaedalusVariableEnum::FUEL => $this->maxFuel,
            DaedalusVariableEnum::HULL => $this->maxHull,
            DaedalusVariableEnum::SHIELD => $this->maxShield,
            DaedalusVariableEnum::HUNTER_POINTS => $this->initHunterPoints,
            DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL => $this->maxCombustionChamberFuel,
            default => throw new \LogicException('this is not a valid daedalusVariable'),
        };
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

    public function getNumberOfProjectsByBatch(): int
    {
        return $this->numberOfProjectsByBatch;
    }

    public function setNumberOfProjectsByBatch(int $numberOfProjectsByBatch): static
    {
        $this->numberOfProjectsByBatch = $numberOfProjectsByBatch;

        return $this;
    }

    public function getHumanSkillSlots(): int
    {
        return $this->humanSkillSlots;
    }

    public function setHumanSkillSlots(int $humanSkillSlots): static
    {
        $this->humanSkillSlots = $humanSkillSlots;

        return $this;
    }

    public function getMushSkillSlots(): int
    {
        return $this->mushSkillSlots;
    }

    public function setMushSkillSlots(int $mushSkillSlots): static
    {
        $this->mushSkillSlots = $mushSkillSlots;

        return $this;
    }

    public function getHoliday(): string
    {
        return $this->holiday;
    }

    public function setHoliday(string $holiday): static
    {
        $this->holiday = match ($holiday) {
            HolidayEnum::CURRENT => $this->getCurrentHoliday(),
            HolidayEnum::ANNIVERSARY => HolidayEnum::ANNIVERSARY,
            HolidayEnum::HALLOWEEN => HolidayEnum::HALLOWEEN,
            HolidayEnum::NONE => HolidayEnum::NONE,
            HolidayEnum::APRIL_FOOLS => HolidayEnum::APRIL_FOOLS,
            default => throw new \LogicException("{$holiday} is not a valid holiday check method"),
        };

        return $this;
    }

    public function getNumberOfCyclesBeforeNextRebelBaseContact(): int
    {
        return $this->numberOfCyclesBeforeNextRebelBaseContact;
    }

    public function setNumberOfCyclesBeforeNextRebelBaseContact(int $numberOfCyclesBeforeNextRebelBaseContact): static
    {
        $this->numberOfCyclesBeforeNextRebelBaseContact = $numberOfCyclesBeforeNextRebelBaseContact;

        return $this;
    }

    public function getRebelBaseContactDurationMin(): int
    {
        return $this->rebelBaseContactDurationMin;
    }

    public function setRebelBaseContactDurationMin(int $rebelBaseContactDurationMin): static
    {
        $this->rebelBaseContactDurationMin = $rebelBaseContactDurationMin;

        return $this;
    }

    public function getRebelBaseContactDurationMax(): int
    {
        return $this->rebelBaseContactDurationMax;
    }

    public function setRebelBaseContactDurationMax(int $rebelBaseContactDurationMax): static
    {
        $this->rebelBaseContactDurationMax = $rebelBaseContactDurationMax;

        return $this;
    }

    public function getStartingRandomBlueprintCount(): int
    {
        return $this->startingRandomBlueprintCount;
    }

    public function setStartingRandomBlueprintCount(int $startingRandomBlueprintCount): static
    {
        $this->startingRandomBlueprintCount = $startingRandomBlueprintCount;

        return $this;
    }

    public function getRandomBlueprints(): ProbaCollection
    {
        return new ProbaCollection($this->randomBlueprints);
    }

    public function setRandomBlueprints(array $randomBlueprints): static
    {
        $this->randomBlueprints = $randomBlueprints;

        return $this;
    }

    public function getCurrentHoliday(): string
    {
        if ($this->isAnniversary()) {
            return HolidayEnum::ANNIVERSARY;
        }

        if ($this->isAprilFools()) {
            return HolidayEnum::APRIL_FOOLS;
        }

        if ($this->isHalloween()) {
            return HolidayEnum::HALLOWEEN;
        }

        return HolidayEnum::NONE;
    }

    public function getPlayerCount(): int
    {
        return $this->playerCount;
    }

    public function setPlayerCount(int $playerCount): static
    {
        $this->playerCount = $playerCount;

        return $this;
    }

    public function getChaolaToggle(): string
    {
        return $this->chaolaToggle;
    }

    public function setChaolaToggle(string $chaolaToggle): static
    {
        $this->chaolaToggle = $chaolaToggle;

        return $this;
    }

    public function getFreeLove(): bool
    {
        return $this->freeLove;
    }

    public function setFreeLove(bool $freeLove): static
    {
        $this->freeLove = $freeLove;

        return $this;
    }

    private function isAnniversary(): bool
    {
        $currentDate = new \DateTime();

        return $currentDate->format('j') >= 3 && $currentDate->format('j') <= 31 && $currentDate->format('F') === 'January';
    }

    private function isAprilFools(): bool
    {
        $currentDate = new \DateTime();

        return $currentDate->format('j') <= 14 && $currentDate->format('F') === 'April';
    }

    private function isHalloween(): bool
    {
        $currentDate = new \DateTime();

        return ($currentDate->format('j') >= 17 && $currentDate->format('F') === 'October') || ($currentDate->format('j') <= 14 && $currentDate->format('F') === 'November');
    }
}
