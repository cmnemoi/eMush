<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Player\Entity\Player;

/**
 * Class StatusEffect
 * @package Mush\Entity
 *
 * @ORM\Entity()
 */
class MedicalCondition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player", inversedBy="medicalConditions")
     */
    private Player $player;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\MedicalConditionConfig")
     */
    private MedicalConditionConfig $medicalConditionConfig;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): MedicalCondition
    {
        $this->player = $player;
        return $this;
    }

    public function getMedicalConditionConfig(): MedicalConditionConfig
    {
        return $this->medicalConditionConfig;
    }

    public function setMedicalConditionConfig(MedicalConditionConfig $medicalConditionConfig): MedicalCondition
    {
        $this->medicalConditionConfig = $medicalConditionConfig;
        return $this;
    }
}
