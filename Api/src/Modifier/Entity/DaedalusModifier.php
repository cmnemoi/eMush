<?php

namespace Mush\Modifier\Entity;

use Mush\Daedalus\Entity\Daedalus;

/**
 * Class Modifier.
 *
 * @ORM\Entity
 */
class DaedalusModifier extends Modifier
{
    /**
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="modifiers")
     */
    private Daedalus $daedalus;

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): DaedalusModifier
    {
        $this->daedalus = $daedalus;

        return $this;
    }
}
