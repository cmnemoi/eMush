<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class GamePlant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Item\Entity\GameFruit", inversedBy="gamePlant")
     */
    private ?GameFruit $gameFruit = null;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameFruit(): ?GameFruit
    {
        return $this->gameFruit;
    }

    public function setGameFruit(GameFruit $gameFruit): GamePlant
    {
        $this->gameFruit = $gameFruit;

        if ($gameFruit->getGamePlant() !== $this) {
            $gameFruit->setGamePlant($this);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GamePlant
    {
        $this->name = $name;
        return $this;
    }

    public function getMaturationTime(): int
    {
        return $this->maturationTime;
    }

    public function setMaturationTime(int $maturationTime): GamePlant
    {
        $this->maturationTime = $maturationTime;
        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    public function setOxygen(int $oxygen): GamePlant
    {
        $this->oxygen = $oxygen;
        return $this;
    }
}
