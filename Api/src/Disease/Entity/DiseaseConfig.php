<?php

namespace Mush\Disease\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Enum\TypeEnum;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_config")
 */
class DiseaseConfig implements LogParameterInterface
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
     */
    private int $resistance = 0;

    /**
     * @ORM\Column (type="array")
     */
    private array $causes = [];

    /**
     * @ORM\Column (type="integer")
     */
    private int $delayMin = 4;

    /**
     * @ORM\Column (type="integer")
     */
    private int $delayLength = 4;

    /**
     * @ORM\Column (type="integer")
     */
    private int $diseasePointMin = 4;

    /**
     * @ORM\Column (type="integer")
     */
    private int $diseasePointLength = 4;

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

    public function getResistance(): int
    {
        return $this->resistance;
    }

    public function setResistance(int $resistance): DiseaseConfig
    {
        $this->resistance = $resistance;

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

    public function getDelayMin(): int
    {
        return $this->delayMin;
    }

    public function setDelayMin(int $delayMin): DiseaseConfig
    {
        $this->delayMin = $delayMin;

        return $this;
    }

    public function getDelayLength(): int
    {
        return $this->delayLength;
    }

    public function setDelayLength(int $delayLength): DiseaseConfig
    {
        $this->delayLength = $delayLength;

        return $this;
    }

    public function getDiseasePointMin(): int
    {
        return $this->diseasePointMin;
    }

    public function setDiseasePointMin(int $diseasePointMin): DiseaseConfig
    {
        $this->diseasePointMin = $diseasePointMin;

        return $this;
    }

    public function getDiseasePointLength(): int
    {
        return $this->diseasePointLength;
    }

    public function setDiseasePointLength(int $diseasePointLength): DiseaseConfig
    {
        $this->diseasePointLength = $diseasePointLength;

        return $this;
    }
}
