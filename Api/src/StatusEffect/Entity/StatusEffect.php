<?php

namespace Mush\StatusEffect\Entity;

use Doctrine\ORM\Mapping as ORM;

/*
  Components are:
  Attributes      id
                  name
                  type
                  duration

  Methods         getId
                  getName, setName
                  getType, setType
                  getDuration, setDuration
*/

/**
 * Class StatusEffect
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\StatusEffect\Repository\StatusEffectRepository")
 */
class StatusEffect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $newName): StatusEffect
    {
        $this->name = $newName;
        return $this;
    }

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $type;
    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $newType): StatusEffect
    {
        // TODO: safety: check whether type exists (is in StatusEffectTypeEnum)
        //if ($newType instanceof StatusEffectTypeEnum)
        $this->type = $newType;

        return $this;
    }
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $duration;
    // Duration is -1 for permanent effects
    public function getDuration(): int
    {
        return $this->duration;
    }
    public function setDuration(int $duration): StatusEffect
    {
        $this->duration = $duration;

        return $this;
    }
}
