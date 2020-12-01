<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PlayerModifier
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getMaxActionPointModifier(): int
    {
        return $this->maxActionPointModifier;
    }

    public function setMaxActionPointModifier(int $maxActionPointModifier): PlayerModifier
    {
        $this->maxActionPointModifier = $maxActionPointModifier;

        return $this;
    }

    public function getMaxMovementPointModifier(): int
    {
        return $this->maxMovementPointModifier;
    }

    public function setMaxMovementPointModifier(int $maxMovementPointModifier): PlayerModifier
    {
        $this->maxMovementPointModifier = $maxMovementPointModifier;

        return $this;
    }

    public function getMaxHealthPointModifier(): int
    {
        return $this->maxHealthPointModifier;
    }

    public function setMaxHealthPointModifier(int $maxHealthPointModifier): PlayerModifier
    {
        $this->maxHealthPointModifier = $maxHealthPointModifier;

        return $this;
    }

    public function getMaxMoralPointModifier(): int
    {
        return $this->maxMoralPointModifier;
    }

    public function setMaxMoralPointModifier(int $maxMoralPointModifier): PlayerModifier
    {
        $this->maxMoralPointModifier = $maxMoralPointModifier;

        return $this;
    }
}
