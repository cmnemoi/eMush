<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Entity\LogParameter;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_config")
 */
class DiseaseConfig implements LogParameter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="string")
     */
    private string $type = TypeEnum::DISEASE;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * Duration is -1 for permanent effects
     */
    private int $duration = -1;

    /**
     * @ORM\Column (type="array")
     */
    private array $causes = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): DiseaseConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DiseaseConfig
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): DiseaseConfig
    {
        $this->type = $type;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): DiseaseConfig
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCauses(): array
    {
        return $this->causes;
    }

    public function setCauses(array $causes): DiseaseConfig
    {
        $this->causes = $causes;

        return $this;
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DISEASE;
    }
}
