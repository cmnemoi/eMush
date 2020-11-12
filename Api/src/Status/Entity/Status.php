<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;

/**
 * Class Status.
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "status" = "Mush\Status\Entity\Status",
 *     "charge_status" = "Mush\Status\Entity\ChargeStatus",
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

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): Status
    {
        if (null === $player && null !== $this->player) {
            $this->player->removeStatus($this);
        } elseif ($this->player !== $player) {
            $player->addStatus($this);
        }

        $this->player = $player;

        return $this;
    }

    public function getGameItem(): ?GameItem
    {
        return $this->gameItem;
    }

    public function setGameItem(?GameItem $gameItem): Status
    {
        if (null === $gameItem && null !== $this->gameItem) {
            $this->gameItem->removeStatus($this);
        } elseif ($this->gameItem !== $gameItem) {
            $gameItem->addStatus($this);
        }

        $this->gameItem = $gameItem;

        return $this;
    }
}
