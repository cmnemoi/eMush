<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class StatusEffect.
 *
 * @ORM\Entity()
 */
class MedicalCondition extends ChargeStatus
{
    /**
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\MedicalConditionConfig")
     */
    private MedicalConditionConfig $medicalConditionConfig;

    public function getMedicalConditionConfig(): MedicalConditionConfig
    {
        return $this->medicalConditionConfig;
    }

    /**
     * @return static
     */
    public function setMedicalConditionConfig(MedicalConditionConfig $medicalConditionConfig): MedicalCondition
    {
        $this->medicalConditionConfig = $medicalConditionConfig;

        return $this;
    }
}
