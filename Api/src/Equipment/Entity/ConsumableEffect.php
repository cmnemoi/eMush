<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\Mechanics\Ration;

/**
 * Class ConsumableEffect.
 *
 * @ORM\Entity
 */
class ConsumableEffect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Equipment\Entity\Mechanics\Ration")
     */
    private Ration $ration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $actionPoint = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $movementPoint = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $healthPoint = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $moralPoint = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $satiety = null;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $extraEffects = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @return static
     */
    public function setDaedalus(Daedalus $daedalus): ConsumableEffect
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getRation(): Ration
    {
        return $this->ration;
    }

    /**
     * @return static
     */
    public function setRation(Ration $ration): ConsumableEffect
    {
        $this->ration = $ration;

        return $this;
    }

    public function getActionPoint(): ?int
    {
        return $this->actionPoint;
    }

    /**
     * @return static
     */
    public function setActionPoint(?int $actionPoint): ConsumableEffect
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }

    public function getMovementPoint(): ?int
    {
        return $this->movementPoint;
    }

    /**
     * @return static
     */
    public function setMovementPoint(?int $movementPoint): ConsumableEffect
    {
        $this->movementPoint = $movementPoint;

        return $this;
    }

    public function getHealthPoint(): ?int
    {
        return $this->healthPoint;
    }

    /**
     * @return static
     */
    public function setHealthPoint(?int $healthPoint): ConsumableEffect
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    public function getMoralPoint(): ?int
    {
        return $this->moralPoint;
    }

    /**
     * @return static
     */
    public function setMoralPoint(?int $moralPoint): ConsumableEffect
    {
        $this->moralPoint = $moralPoint;

        return $this;
    }

    public function getSatiety(): ?int
    {
        return $this->satiety;
    }

    /**
     * @return static
     */
    public function setSatiety(?int $satiety): ConsumableEffect
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function getExtraEffects(): array
    {
        return $this->extraEffects;
    }

    /**
     * @return static
     */
    public function setExtraEffects(array $extraEffects): ConsumableEffect
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }
}
