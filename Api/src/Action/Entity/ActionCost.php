<?php

namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ActionCost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $actionPointCost = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $movementPointCost = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $moralPointCost = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionPointCost(): ?int
    {
        return $this->actionPointCost;
    }

    public function setActionPointCost(?int $actionPointCost): ActionCost
    {
        $this->actionPointCost = $actionPointCost;

        return $this;
    }

    public function getMovementPointCost(): ?int
    {
        return $this->movementPointCost;
    }

    public function setMovementPointCost(?int $movementPointCost): ActionCost
    {
        $this->movementPointCost = $movementPointCost;

        return $this;
    }

    public function getMoralPointCost(): ?int
    {
        return $this->moralPointCost;
    }

    public function setMoralPointCost(?int $moralPointCost): ActionCost
    {
        $this->moralPointCost = $moralPointCost;

        return $this;
    }
}
