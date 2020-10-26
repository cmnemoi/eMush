<?php


namespace Mush\Item\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

/**
 * Class Item
 * @package Mush\Entity
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "item" = "GameItem",
 *     "fruit" = "GameFruit",
 *     "plant" = "GamePlant"
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
     * @ORM\Column(type="array", nullable=false)
     */
    private ?array $statuses = null;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Room\Entity\Room", inversedBy="items")
     */
    private ?Room $room = null;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player", inversedBy="items")
     */
    private ?Player $player = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Item")
     */
    private Item $item;


    private string $type;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatuses(): ?array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): GameItem
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function addStatus(string $status): GameItem
    {
        $this->statuses[] = $status;

        return $this;
    }

    public function removeStatus(string $status): GameItem
    {
        $this->statuses = array_diff($this->getStatuses(), [$status]);
        return $this;
    }

    public function hasStatus(string $status): bool
    {
        return in_array($status, $this->getStatuses());
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): GameItem
    {
        if ($room === null) {
            $this->room->removeItem($this);
        } elseif ($this->room !== $room) {
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
        if ($player === null) {
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): GameItem
    {
        $this->type = $type;
        return $this;
    }
}
