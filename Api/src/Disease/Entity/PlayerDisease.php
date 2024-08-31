<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Player\Entity\Player;

#[ORM\Entity]
#[ORM\Table(name: 'disease_player')]
class PlayerDisease
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DiseaseConfig::class)]
    private DiseaseConfig $diseaseConfig;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'diseases')]
    private Player $player;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $status = DiseaseStatusEnum::ACTIVE;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $diseasePoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $resistancePoint = 0;

    public static function createNull(): self
    {
        $disease = new self();
        $disease->diseaseConfig = new DiseaseConfig();
        $disease->player = Player::createNull();

        return $disease;
    }

    public function getId(): ?int
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

    public function getDiseasePoint(): int
    {
        return $this->diseasePoint;
    }

    public function setDiseasePoint(int $diseasePoint): self
    {
        $this->diseasePoint = $diseasePoint;

        return $this;
    }

    public function decrementDiseasePoints(): self
    {
        --$this->diseasePoint;

        return $this;
    }

    public function getResistancePoint(): int
    {
        return $this->resistancePoint;
    }

    public function setResistancePoint(int $resistancePoint): self
    {
        $this->resistancePoint = $resistancePoint;

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
        return $this->isAPhysicalDisease() || $this->getName() === DisorderEnum::SPLEEN || $this->getName() === DisorderEnum::VERTIGO;
    }

    public function isIncubating(): bool
    {
        return $this->getStatus() === DiseaseStatusEnum::INCUBATING;
    }

    public function isAnInjury(): bool
    {
        return $this->diseaseConfig->getType() === MedicalConditionTypeEnum::INJURY;
    }
}
