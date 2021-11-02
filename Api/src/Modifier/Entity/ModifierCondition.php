<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Modifier.
 *
 * @ORM\Entity
 * @ORM\Table(name="modifier_condition")
 */
class ModifierCondition
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
     * @ORM\Column(type="string", nullable=false)
     */
    private string $condition;

    public function __construct(string $name, string $condition)
    {
        $this->condition = $condition;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }
}
