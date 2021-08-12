<?php

namespace Mush\Modifier\Entity;

use Mush\Status\Entity\ChargeStatus;

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
     * @ORM\ManyToOne (targetEntity="Mush\Modifier\Entity\ModifierConfig")
     */
    private ModifierConfig $modifierConfig;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Status\Entity\ChargeStatus")
     */
    private ?ChargeStatus $charge = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModifierConfig(): ModifierConfig
    {
        return $this->modifierConfig;
    }

    public function setModifierConfig(ModifierConfig $modifierConfig): Modifier
    {
        $this->modifierConfig = $modifierConfig;

        return $this;
    }

    public function getCharge(): ?ChargeStatus
    {
        return $this->charge;
    }

    public function setCharge(ChargeStatus $charge): Modifier
    {
        $this->charge = $charge;

        return $this;
    }
}
