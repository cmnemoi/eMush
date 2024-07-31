<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\Common\Collections\ArrayCollection;

interface SkinableConfigInterface
{
    public function getSkinSlotsConfig(): ArrayCollection;
}
