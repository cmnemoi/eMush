<?php

namespace Mush\Modifier\Entity;

use Mush\Place\Entity\Place;

/**
 * Class Modifier.
 *
 * @ORM\Entity
 */
class PlaceModifier extends Modifier
{
    /**
     * @ORM\ManyToOne (targetEntity="Mush\Place\Entity\Place", inversedBy="modifiers")
     */
    private Place $place;

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function setPlace(Place $place): PlaceModifier
    {
        $this->place = $place;

        return $this;
    }
}
