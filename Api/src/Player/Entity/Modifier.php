<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $delta = 0;

    /**
     * @ORM\Column(type="target", nullable=false)
     */
    private string $target;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDelta(): int
    {
        return $this->delta;
    }

    public function setDelta(int $delta): Modifier
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
}
