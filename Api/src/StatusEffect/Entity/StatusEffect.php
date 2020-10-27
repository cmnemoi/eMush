<?php

namespace Mush\StatusEffect\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\StatusEffect\Enum\StatusEffectTypeEnum;


/**
 * Class StatusEffect
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\StatusEffect\Repository\StatusEffectRepository")
 */

/*
  Components are:
  Attributes      id
                  name
                  type
                  duration

  Methods         getId, setId
                  getName, setName
                  getType, setType
                  getDuration, setDuration
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
    public function setId($newId): void
    {
      $this->id = $newId;
    }

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;
    public function getName(): string
    {
      return $this->name;
    }
    public function setName($newName): void
    {
      $this->name = $newName;
    }

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $type;
    public function getType(): string
    {
      return $this->id;
    }
    public function setType($newType): void
    {
      // TODO: safety: check whether type exists (is in StatusEffectTypeEnum)
      //if ($newType instanceof StatusEffectTypeEnum)
          $this->type = $newType;
    }
    /**
     * @ORM\Column(type="int", nullable=false)
     */
    private int $duration;
    // Duration is -1 for permanent effects
    public function getDuration(): int
    {
      return $this->duration;
    }
    public function setDuration($newId): int
    {
      $this->duration = $newId;
    }



}
