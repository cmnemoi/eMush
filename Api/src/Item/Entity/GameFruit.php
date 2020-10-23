<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @ORM\Entity()
 */
class GameFruit
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
     *     targetEntity="Mush\Item\Entity\GamePlant",
     *     cascade={"ALL"},
     *     orphanRemoval=true,
     *      inversedBy="gameFruit"
     * )
     */
    private ?GamePlant $gamePlant = null;

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

    public function setDaedalus(Daedalus $daedalus): GameFruit
    {
        $this->daedalus = $daedalus;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GameFruit
    {
        $this->name = $name;
        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $actionPoint): GameFruit
    {
        $this->actionPoint = $actionPoint;
        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(int $healthPoint): GameFruit
    {
        $this->healthPoint = $healthPoint;
        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->moralPoint;
    }

    public function setMoralPoint(int $moralPoint): GameFruit
    {
        $this->moralPoint = $moralPoint;
        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    public function setSatiety(int $satiety): GameFruit
    {
        $this->satiety = $satiety;
        return $this;
    }

    public function getCures(): array
    {
        return $this->cures;
    }

    public function setCures(array $cures): GameFruit
    {
        $this->cures = $cures;
        return $this;
    }

    public function getDiseases(): array
    {
        return $this->diseases;
    }

    public function setDiseases(array $diseases): GameFruit
    {
        $this->diseases = $diseases;
        return $this;
    }

    public function getGamePlant(): ?GamePlant
    {
        return $this->gamePlant;
    }

    public function setGamePlant(GamePlant $gamePlant): GameFruit
    {
        $this->gamePlant = $gamePlant;
        if ($gamePlant->getGameFruit() !== $this) {
            $gamePlant->setGameFruit($this);
        }
        return $this;
    }
}
