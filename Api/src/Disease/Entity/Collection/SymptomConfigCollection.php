<?php

namespace Mush\Disease\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Disease\Entity\Config\SymptomConfig;

class SymptomConfigCollection extends ArrayCollection
{
    public function getTriggeredSymptoms(array $triggers): self
    {
        return $this->filter(fn (SymptomConfig $symptomConfig) => in_array($symptomConfig->getTrigger(), $triggers));
    }

    public function getSymptomFromConfig(SymptomConfig $symptomConfig): string
    {
        return $this->filter(fn (SymptomConfig $symptomConfig) => $symptomConfig->getName())->first();
    }

    public function hasSymptomByName(string $name): bool
    {
        return !$this->filter(fn (SymptomConfig $symptomConfig) => $symptomConfig->getName() === $name)->isEmpty();
    }
}
