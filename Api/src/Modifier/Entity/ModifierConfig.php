<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Enum\ModifierModeEnum;

/**
 * @ORM\Entity
 * @ORM\Table(name="modifier_config")
 */
class ModifierConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private float $delta = 0;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $target;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $scope;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $reach = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $mode = ModifierModeEnum::ADDITIVE;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Modifier\Entity\ModifierCondition")
     */
    private Collection $modifierConditions;

    public function __construct()
    {
        $this->modifierConditions = new ArrayCollection([]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): self
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getDelta(): float
    {
        return $this->delta;
    }

    public function setDelta(float $delta): self
    {
        $this->delta = $delta;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getReach(): ?string
    {
        return $this->reach;
    }

    public function setReach(string $reach): self
    {
        $this->reach = $reach;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getModifierConditions(): Collection
    {
        return $this->modifierConditions;
    }

    public function addModifierConditions(ModifierCondition $modifierCondition): self
    {
        $this->modifierConditions->add($modifierCondition);

        return $this;
    }
}
