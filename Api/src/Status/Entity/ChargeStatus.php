<?php


namespace Mush\Status\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChargeStatus
 * @ORM\Entity
 */
class ChargeStatus extends Status
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $charge = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $strategy = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $threshold = null;

    public function getCharge(): ?int
    {
        return $this->charge;
    }

    public function addCharge(int $charge): Status
    {
        $this->charge += $charge;

        return $this;
    }

    public function setCharge(?int $charge): Status
    {
        $this->charge = $charge;

        return $this;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    public function setStrategy(?string $strategy): Status
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    public function setThreshold(?int $threshold): Status
    {
        $this->threshold = $threshold;

        return $this;
    }
}