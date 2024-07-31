<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\Common\Collections\ArrayCollection;

interface SkinableEntityInterface
{
    public function getSkinSlots(): ArrayCollection;
}
