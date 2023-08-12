<?php

namespace Mush\Disease\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Disease\Entity\Config\SymptomConfig;

/**
 * @template-extends ArrayCollection<int, SymptomConfig>
 */
class SymptomConfigCollection extends ArrayCollection
{
    public function getTriggeredSymptoms(array $triggers): self
    {
        return $this->filter(fn (SymptomConfig $symptomConfig) => in_array($symptomConfig->getTrigger(), $triggers));
    }

    public function hasSymptomByName(string $name): bool
    {
        return !$this->filter(fn (SymptomConfig $symptomConfig) => $symptomConfig->getEventName() === $name)->isEmpty();
    }
}
