<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

/**
 * Class Item
 * @package Mush\Entity
 *
 * @ORM\Entity
 */
class Fruit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\Daedalus")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Mush\Item\Entity\Plant",
     *     cascade={"ALL"},
     *     orphanRemoval=true,
     *      inversedBy="fruit"
     * )
     */
    private ?Plant $plant = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $healthPoint;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private int $moralPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $satiety = 1;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $cures = [];

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $diseases = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): Fruit
    {
        $this->daedalus = $daedalus;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Fruit
    {
        $this->name = $name;
        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $actionPoint): Fruit
    {
        $this->actionPoint = $actionPoint;
        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(int $healthPoint): Fruit
    {
        $this->healthPoint = $healthPoint;
        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->moralPoint;
    }

    public function setMoralPoint(int $moralPoint): Fruit
    {
        $this->moralPoint = $moralPoint;
        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    public function setSatiety(int $satiety): Fruit
    {
        $this->satiety = $satiety;
        return $this;
    }

    public function getCures(): array
    {
        return $this->cures;
    }

    public function setCures(array $cures): Fruit
    {
        $this->cures = $cures;
        return $this;
    }

    public function getDiseases(): array
    {
        return $this->diseases;
    }

    public function setDiseases(array $diseases): Fruit
    {
        $this->diseases = $diseases;
        return $this;
    }

    public function getPlant(): ?Plant
    {
        return $this->plant;
    }

    public function setPlant(Plant $plant): Fruit
    {
        $this->plant = $plant;
        if ($plant->getFruit() !== $this) {
            $plant->setFruit($this);
        }
        return $this;
    }
}
