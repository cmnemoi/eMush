<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseStatusEnum;
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

    public function getResistancePoint(): int
    {
        return $this->resistancePoint;
    }

    public function setResistancePoint(int $resistancePoint): self
    {
        $this->resistancePoint = $resistancePoint;

        return $this;
    }
}
