<?php


namespace Mush\Status\Entity;


use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;

/**
 * Class Status
 * @package Mush\Status\Entity
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "status" = "Mush\Status\Entity\Status",
 *     "attempt" = "Mush\Status\Entity\Attempt",
 *     "medical_condition" = "Mush\Status\Entity\MedicalCondition",
 * })
 */
class Status
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected ?string $name = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected ?string $visibility = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player", inversedBy="statuses")
     */
    protected ?Player $player = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Item\Entity\GameItem", inversedBy="statuses")
     */
    protected ?GameItem $gameItem = null;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Status
    {
        $this->name = $name;
        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(?string $visibility): Status
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): Status
    {
        $this->player = $player;
        return $this;
    }

    public function getGameItem(): ?GameItem
    {
        return $this->gameItem;
    }

    public function setGameItem(?GameItem $gameItem): Status
    {
        $this->gameItem = $gameItem;
        return $this;
    }

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