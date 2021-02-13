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
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionPointCost = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $movementPointCost = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $moralPointCost = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionPointCost(): ?int
    {
        return $this->actionPointCost;
    }

    public function addActionPointCost(int $actionPoint): ActionCost
    {
        $this->actionPointCost += $actionPoint;

        return $this;
    }

    public function setActionPointCost(int $actionPointCost): ActionCost
    {
        $this->actionPointCost = $actionPointCost;

        return $this;
    }

    public function getMovementPointCost(): ?int
    {
        return $this->movementPointCost;
    }

    public function addMovementPointCost(int $movementPoint): ActionCost
    {
        $this->movementPointCost += $movementPoint;

        return $this;
    }

    public function setMovementPointCost(int $movementPointCost): ActionCost
    {
        $this->movementPointCost = $movementPointCost;

        return $this;
    }

    public function getMoralPointCost(): ?int
    {
        return $this->moralPointCost;
    }

    public function addMoralPointPointCost(int $moralPointCost): ActionCost
    {
        $this->moralPointCost += $moralPointCost;

        return $this;
    }

    public function setMoralPointCost(int $moralPointCost): ActionCost
    {
        $this->moralPointCost = $moralPointCost;

        return $this;
    }
}
