<?php

namespace Mush\Situation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @ORM\Entity
 * @ORM\Table(name="situation")
 */
class Situation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="situations")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isVisible;

    public function __construct(Daedalus $daedalus, string $name, bool $isVisible)
    {
        $this->daedalus = $daedalus;
        $this->name = $name;
        $this->isVisible = $isVisible;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setIsVisible(bool $isVisible): Situation
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }
}
