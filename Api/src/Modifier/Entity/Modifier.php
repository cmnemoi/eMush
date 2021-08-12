<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Status\Entity\ChargeStatus;

/**
 * Class Modifier.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "player_modifier" = "Mush\Modifier\Entity\PlayerModifier",
 *     "daedalus_modifier" = "Mush\Modifier\Entity\DaedalusModifier",
 *     "place_modifier" = "Mush\Modifier\Entity\PlaceModifier",
 *     "equipment_modifier" = "Mush\Modifier\Entity\EquipmentModifier",
 * })
 */
abstract class Modifier
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
     * @ORM\OneToOne (targetEntity="Mush\Status\Entity\ChargeStatus")
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
