<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\PlayerModifier;

/**
 * Class Condition.
 *
 * @ORM\Entity(repositoryClass="Mush\Status\Repository\MedicalConditionConfigRepository")
 */
class MedicalConditionConfig
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
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig")
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
     * @ORM\OneToOne (targetEntity="Mush\Player\Entity\ActionModifier", cascade={"ALL"}, orphanRemoval=true)
     */
    private ?ActionModifier $actionModifier = null;

    /**
     * @ORM\OneToOne (targetEntity="Mush\Player\Entity\PlayerModifier", cascade={"ALL"}, orphanRemoval=true)
     */
    private ?PlayerModifier $playerModifier = null;

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

    /**
     * @return static
     */
    public function setName(string $newName): MedicalConditionConfig
    {
        $this->name = $newName;

        return $this;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    /**
     * @return static
     */
    public function setGameConfig(GameConfig $gameConfig): MedicalConditionConfig
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return static
     */
    public function setType(string $newType): MedicalConditionConfig
    {
        $this->type = $newType;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return static
     */
    public function setDuration(int $duration): MedicalConditionConfig
    {
        $this->duration = $duration;

        return $this;
    }

    public function getActionModifier(): ?ActionModifier
    {
        return $this->actionModifier;
    }

    /**
     * @return static
     */
    public function setActionModifier(?ActionModifier $actionModifier): MedicalConditionConfig
    {
        $this->actionModifier = $actionModifier;

        return $this;
    }

    public function getPlayerModifier(): ?PlayerModifier
    {
        return $this->playerModifier;
    }

    /**
     * @return static
     */
    public function setPlayerModifier(?PlayerModifier $playerModifier): MedicalConditionConfig
    {
        $this->playerModifier = $playerModifier;

        return $this;
    }

    public function getSymptoms(): array
    {
        return $this->symptoms;
    }

    /**
     * @return static
     */
    public function setSymptoms(array $symptoms): MedicalConditionConfig
    {
        $this->symptoms = $symptoms;

        return $this;
    }
}
