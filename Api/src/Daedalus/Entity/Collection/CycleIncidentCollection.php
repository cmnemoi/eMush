<?php

declare(strict_types=1);

namespace Mush\Daedalus\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Enum\CycleIncidentEnum;
use Mush\Daedalus\ValueObject\CycleIncident;
use Mush\Game\Entity\Collection\ProbaCollection;

/**
 * @template-extends ArrayCollection<int, CycleIncident>
 */
final class CycleIncidentCollection extends ArrayCollection
{
    public function getByNameOrThrow(CycleIncidentEnum $name): CycleIncident
    {
        return $this->filter(static fn (CycleIncident $incident) => $incident->name === $name)->first()
            ?: throw new \LogicException("Incident {$name->value} not found");
    }

    public function getWeights(): ProbaCollection
    {
        $weights = new ProbaCollection();
        foreach ($this as $incident) {
            $weights->setElementProbability($incident->name->toString(), $incident->name->getWeight());
        }

        return $weights;
    }
}
