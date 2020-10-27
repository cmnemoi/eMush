<?php


namespace Mush\RoomLog\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

/**
 * Class RoomLog
 * @package Mush\Entity
 *
 * @ORM\Entity(repositoryClass="Mush\RoomLog\Repository\RoomLogRepository")
 */
class RoomLog
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Room\Entity\Room")
     */
    private Room $room;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player")
     */
    private Player $player;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Item\Entity\GameItem")
     */
    private GameItem $item;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $visibility;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $log;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTime $date;

    /**
     * @ORM\Column(type="array", length=255, nullable=false)
     */
    private array $params;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): RoomLog
    {
        $this->room = $room;
        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): RoomLog
    {
        $this->player = $player;
        return $this;
    }

    public function getItem(): GameItem
    {
        return $this->item;
    }

    public function setItem(GameItem $item): RoomLog
    {
        $this->item = $item;
        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): RoomLog
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function setLog(string $log): RoomLog
    {
        $this->log = $log;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): RoomLog
    {
        $this->date = $date;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): RoomLog
    {
        $this->params = $params;
        return $this;
    }
}
