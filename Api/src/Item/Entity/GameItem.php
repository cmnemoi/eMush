<?php

namespace Mush\Item\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Enum\StatusEnum;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;

/**
 * Class Item.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "game_item" = "Mush\Item\Entity\GameItem",
 *     "door" = "Mush\Item\Entity\Door",
 * })
 */
class GameItem
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Mush\Status\Entity\Status",
     *     mappedBy="gameItem",
     *     cascade={"ALL"},
     *     orphanRemoval=true
     *     )
     */
    private Collection $statuses;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Room\Entity\Room", inversedBy="items")
     */
    private ?Room $room = null;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player", inversedBy="items")
     */
    private ?Player $player = null;

    /**
     * @ORM\ManyToOne(targetEntity="Item")
     */
    private Item $item;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * GameItem constructor.
     */
    public function __construct()
    {
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getActions(): Collection
    {
        return $this->item->getActions();
    }

    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function setStatuses(Collection $statuses): GameItem
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function addStatus(Status $status): GameItem
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses->add($status);
            $status->setGameItem($this);
        }

        return $this;
    }

    public function removeStatus(Status $status): GameItem
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            $status->setGameItem(null);
        }

        return $this;
    }

    public function getStatusByName(string $name): ?Status
    {
        $status = $this->statuses->filter(fn (Status $status) => ($status->getName() === $name))->first();

        return $status ? $status : null;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): GameItem
    {
        if (null === $room) {
            $this->room->removeItem($this);
        } elseif ($this->room !== $room) {
            if ($this->getRoom() !== null) {
                $this->getRoom()->removeItem($this);
            }
            $room->addItem($this);
        }

        $this->room = $room;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): GameItem
    {
        if (null === $player && null !== $this->player) {
            $this->player->removeItem($this);
        } elseif ($this->player !== $player) {
            $player->addItem($this);
        }

        $this->player = $player;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GameItem
    {
        $this->name = $name;

        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): GameItem
    {
        $this->item = $item;

        return $this;
    }

    public function isBroken(): bool
    {
        return $this->getStatuses()->exists(fn(int $key, Status $status) => ($status->getName() === StatusEnum::BROKEN));
    }

    public function getBrokenRate(): int
    {
        return $this->getItem()->getBreakableRate();
    }
}
