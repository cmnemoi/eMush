<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

#[ORM\Entity]
#[ORM\Table(name: 'disease_cause_config')]
class DiseaseCauseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GameConfig::class)]
    private GameConfig $gameConfig;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $causeName;

    #[ORM\Column(type: 'array')]
    private array $diseases = [];

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): self
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->causeName;
    }

    public function setName(string $causeName): self
    {
        $this->causeName = $causeName;

        return $this;
    }

    public function getDiseases(): array
    {
        return $this->diseases;
    }

    public function setDiseases(array $diseases): self
    {
        $this->diseases = $diseases;

        return $this;
    }
}
