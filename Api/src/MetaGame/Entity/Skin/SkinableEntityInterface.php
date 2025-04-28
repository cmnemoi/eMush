<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\Common\Collections\ArrayCollection;

interface SkinableEntityInterface
{
    public function getSkinSlots(): ArrayCollection;

    public function getSkinSlotByName(string $name): ?SkinSlot;

    public function initializeSkinSlots(SkinableConfigInterface $skinableConfig): static;
}
