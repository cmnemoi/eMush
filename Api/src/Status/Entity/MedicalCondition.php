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
class MedicalCondition extends Status
{
    /**
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\MedicalConditionConfig")
     */
    private MedicalConditionConfig $medicalConditionConfig;

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
