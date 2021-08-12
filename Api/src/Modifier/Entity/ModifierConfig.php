<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isAdditive = true;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDelta(): float
    {
        return $this->delta;
    }

    public function setDelta(float $delta): ModifierConfig
    {
        $this->delta = $delta;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): ModifierConfig
    {
        $this->scope = $scope;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): ModifierConfig
    {
        $this->target = $target;

        return $this;
    }

    public function getReach(): ?string
    {
        return $this->reach;
    }

    public function setReach(string $reach): ModifierConfig
    {
        $this->reach = $reach;

        return $this;
    }

    public function isAdditive(): bool
    {
        return $this->isAdditive;
    }

    public function setIsAdditive(bool $isAdditive): ModifierConfig
    {
        $this->isAdditive = $isAdditive;

        return $this;
    }
}
