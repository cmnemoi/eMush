<?php

namespace Mush\MetaGame\Entity\Skin;

use Doctrine\Common\Collections\ArrayCollection;

trait SkinableEntityTrait
{
    public function getSkinSlots(): ArrayCollection
    {
        return new ArrayCollection($this->skinSlots->toArray());
    }

    public function getSkinSlotByName(string $name): ?SkinSlot
    {
        $skinSlots = $this->skinSlots->filter(static fn (SkinSlot $slot) => $slot->getName() === $name);

        if ($skinSlots->count() === 0) {
            return null;
        }

        return $skinSlots->first();
    }

    public function initializeSkinSlots(SkinableConfigInterface $skinableConfig): static
    {
        $skinSlots = [];
        foreach ($skinableConfig->getSkinSlotsConfig() as $skinSlotConfig) {
            $skinSlot = new SkinSlot();
            $skinSlot
                ->setNameFromConfig($skinSlotConfig)
                ->setSkinableEntity($this);

            $skinSlots[] = $skinSlot;
        }
        $this->skinSlots = new ArrayCollection($skinSlots);

        return $this;
    }
}
