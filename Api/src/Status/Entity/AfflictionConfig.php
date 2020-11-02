<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;

/**
 * Class Condition
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\Status\Repository\AfflictionConfigRepository")
 */
class AfflictionConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="itemsConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $type;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * Duration is -1 for permanent effects
     */
    private int $duration;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $movementPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $moralPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $precisionModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxActionPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxMovementPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxHealthPointModifier = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $maxMoralPointModifier = 0;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $symptoms = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $newName): AfflictionConfig
    {
        $this->name = $newName;
        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): AfflictionConfig
    {
        $this->gameConfig = $gameConfig;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $newType): AfflictionConfig
    {
        $this->type = $newType;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): AfflictionConfig
    {
        $this->duration = $duration;

        return $this;
    }

    public function getActionPointModifier(): int
    {
        return $this->actionPointModifier;
    }

    public function setActionPointModifier(int $actionPointModifier): AfflictionConfig
    {
        $this->actionPointModifier = $actionPointModifier;
        return $this;
    }

    public function getMovementPointModifier(): int
    {
        return $this->movementPointModifier;
    }

    public function setMovementPointModifier(int $movementPointModifier): AfflictionConfig
    {
        $this->movementPointModifier = $movementPointModifier;
        return $this;
    }

    public function getMoralPointModifier(): int
    {
        return $this->moralPointModifier;
    }

    public function setMoralPointModifier(int $moralPointModifier): AfflictionConfig
    {
        $this->moralPointModifier = $moralPointModifier;
        return $this;
    }

    public function getPrecisionModifier(): int
    {
        return $this->precisionModifier;
    }

    public function setPrecisionModifier(int $precisionModifier): AfflictionConfig
    {
        $this->precisionModifier = $precisionModifier;
        return $this;
    }

    public function getMaxActionPointModifier(): int
    {
        return $this->maxActionPointModifier;
    }

    public function setMaxActionPointModifier(int $maxActionPointModifier): AfflictionConfig
    {
        $this->maxActionPointModifier = $maxActionPointModifier;
        return $this;
    }

    public function getMaxMovementPointModifier(): int
    {
        return $this->maxMovementPointModifier;
    }

    public function setMaxMovementPointModifier(int $maxMovementPointModifier): AfflictionConfig
    {
        $this->maxMovementPointModifier = $maxMovementPointModifier;
        return $this;
    }

    public function getMaxHealthPointModifier(): int
    {
        return $this->maxHealthPointModifier;
    }

    public function setMaxHealthPointModifier(int $maxHealthPointModifier): AfflictionConfig
    {
        $this->maxHealthPointModifier = $maxHealthPointModifier;
        return $this;
    }

    public function getMaxMoralPointModifier(): int
    {
        return $this->maxMoralPointModifier;
    }

    public function setMaxMoralPointModifier(int $maxMoralPointModifier): AfflictionConfig
    {
        $this->maxMoralPointModifier = $maxMoralPointModifier;
        return $this;
    }

    public function getSymptoms(): array
    {
        return $this->symptoms;
    }

    public function setSymptoms(array $symptoms): AfflictionConfig
    {
        $this->symptoms = $symptoms;
        return $this;
    }
}
