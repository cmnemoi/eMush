<?php


namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class RandomItemPlaces
 * @package Mush\Daedalus\Entity
 * @ORM\Entity()
 * @ORM\Table(name="config_random_item_place")
 */
class RandomItemPlaces
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $places;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $items;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setPlaces(array $places): RandomItemPlaces
    {
        $this->places = $places;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): RandomItemPlaces
    {
        $this->items = $items;
        return $this;
    }
}
