<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class HiddenStatus.
 *
 * @ORM\Entity
 */
class HiddenStatus extends Status
{
    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player", inversedBy="statuses")
     */
    private ?player $hiddenBy = null;


    public function getHiddenBy(): ?player
    {
        return $this->hiddenBy;
    }

    public function setHiddenBy (int $hiddenBy): Status
    {
        $this->hiddenBy = $hiddenBy;

        return $this;
    }
}
