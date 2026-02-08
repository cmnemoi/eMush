<?php

namespace Mush\Disease\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;

#[ORM\Entity]
#[ORM\Table(name: 'disease_player')]
class PlayerDisease implements ModifierProviderInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: DiseaseConfig::class)]
    private DiseaseConfig $diseaseConfig;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'diseases')]
    private Player $player;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $status = DiseaseStatusEnum::ACTIVE;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $duration = 0;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $healActionResistance = 1;

    public function __construct()
    {
        $this->modifierConfigs = new ArrayCollection();
    }

    public static function createNull(): self
    {
        $disease = new self();
        $disease->diseaseConfig = new DiseaseConfig();
        $disease->player = Player::createNull();

        return $disease;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->diseaseConfig;
    }

    public function setDiseaseConfig(DiseaseConfig $diseaseConfig): self
    {
        $this->diseaseConfig = $diseaseConfig;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function decrementDuration(): self
    {
        --$this->duration;

        return $this;
    }

    public function getHealActionResistance(): int
    {
        return $this->healActionResistance;
    }

    public function setHealActionResistance(int $healActionResistance): self
    {
        $this->healActionResistance = $healActionResistance;

        return $this;
    }

    public function decrementHealActionResistance(): self
    {
        --$this->healActionResistance;

        return $this;
    }

    public function getName(): string
    {
        return $this->diseaseConfig->getDiseaseName();
    }

    public function isActive(): bool
    {
        return $this->status === DiseaseStatusEnum::ACTIVE;
    }

    /**
     * Returns true if the disease is a disorder and the player is lying down in a shrink room.
     */
    public function isTreatedByAShrink(): bool
    {
        return $this->isADisorder() && $this->player->isLaidDownInShrinkRoom();
    }

    public function isADisorder(): bool
    {
        return $this->diseaseConfig->getType() === MedicalConditionTypeEnum::DISORDER;
    }

    public function isAPhysicalDisease(): bool
    {
        return $this->diseaseConfig->getType() === MedicalConditionTypeEnum::DISEASE;
    }

    public function healsAtCycleChange(): bool
    {
        return $this->diseaseConfig->canNaturalHeal() && $this->isAnInjury() === false;
    }

    public function isIncubating(): bool
    {
        return $this->getStatus() === DiseaseStatusEnum::INCUBATING;
    }

    public function isAnInjury(): bool
    {
        return $this->diseaseConfig->getType() === MedicalConditionTypeEnum::INJURY;
    }

    public function shouldHealSilently(): bool
    {
        return $this->player->isMush() && ($this->isAPhysicalDisease() || $this->isIncubating());
    }

    public function getUsedCharge(string $actionName): ?ChargeStatus
    {
        return null;
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        if ($this->isActive()) {
            return ActionProviderOperationalStateEnum::OPERATIONAL;
        }

        return ActionProviderOperationalStateEnum::DEACTIVATED;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    /**
     * @param array<int, AbstractModifierConfig>|Collection<int, AbstractModifierConfig> $modifierConfigs
     */
    public function setModifierConfigs(array|Collection $modifierConfigs): self
    {
        if (\is_array($modifierConfigs)) {
            $modifierConfigs = new ArrayCollection($modifierConfigs);
        }

        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }

    public function getAllModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->getModifierConfigs()->toArray());
    }

    public function hasModifier(string $modifierName): bool
    {
        $modifier = $this->getAllModifierConfigs()->findFirst(static fn ($key, $modifierConfig): bool => $modifierConfig->getName() === $modifierName);

        return $modifier !== null;
    }
}
