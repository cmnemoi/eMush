<?php

namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ConsumableModifier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getActionPointModifier(): int
    {
        return $this->actionPointModifier;
    }

    public function setActionPointModifier(int $actionPointModifier): ConsumableModifier
    {
        $this->actionPointModifier = $actionPointModifier;

        return $this;
    }

    public function getMovementPointModifier(): int
    {
        return $this->movementPointModifier;
    }

    public function setMovementPointModifier(int $movementPointModifier): ConsumableModifier
    {
        $this->movementPointModifier = $movementPointModifier;

        return $this;
    }

    public function getMoralPointModifier(): int
    {
        return $this->moralPointModifier;
    }

    public function setMoralPointModifier(int $moralPointModifier): ConsumableModifier
    {
        $this->moralPointModifier = $moralPointModifier;

        return $this;
    }

    public function getPrecisionModifier(): int
    {
        return $this->precisionModifier;
    }

    public function setPrecisionModifier(int $precisionModifier): ConsumableModifier
    {
        $this->precisionModifier = $precisionModifier;

        return $this;
    }
}
