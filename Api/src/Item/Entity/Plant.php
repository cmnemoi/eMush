<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Plant extends Item
{
    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\Fruit", inversedBy=")
     */
    private ?Fruit $fruit = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $maturationTime;

    /**
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $oxygen;

    public function getFruit(): ?Fruit
    {
        return $this->fruit;
    }

    public function setFruit(Fruit $fruit): Plant
    {
        $this->fruit = $fruit;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Plant
    {
        $this->name = $name;
        return $this;
    }

    public function getMaturationTime(): int
    {
        return $this->maturationTime;
    }

    public function setMaturationTime(int $maturationTime): Plant
    {
        $this->maturationTime = $maturationTime;
        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    public function setOxygen(int $oxygen): Plant
    {
        $this->oxygen = $oxygen;
        return $this;
    }
}
