<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'config_random_item_place')]
class RandomItemPlaces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $places;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $items;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    /**
     * @return static
     */
    public function setPlaces(array $places): self
    {
        $this->places = $places;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return static
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }
}
