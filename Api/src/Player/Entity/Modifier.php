<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @TODO: Move that to Equipment directory
 */
class Modifier
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
    private ?string $scope = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $reach = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isAdditive = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDelta(): float
    {
        return $this->delta;
    }

    public function setDelta(float $delta): Modifier
    {
        $this->delta = $delta;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): Modifier
    {
        $this->target = $target;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): Modifier
    {
        $this->scope = $scope;

        return $this;
    }

    public function getReach(): ?string
    {
        return $this->reach;
    }

    public function setReach(?string $reach): Modifier
    {
        $this->reach = $reach;

        return $this;
    }

    public function isAdditive(): ?bool
    {
        return $this->isAdditive;
    }

    public function setIsAdditive(?bool $isAdditive): Modifier
    {
        $this->isAdditive = $isAdditive;

        return $this;
    }
}
