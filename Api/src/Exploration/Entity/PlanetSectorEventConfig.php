<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
class PlanetSectorEventConfig extends AbstractEventConfig
{
    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $outputQuantityTable = null;

    public function getOutputQuantityTable(): ?ProbaCollection
    {
        if ($this->outputQuantityTable === null) {
            return null;
        }

        return new ProbaCollection($this->outputQuantityTable);
    }

    public function setOutputQuantityTable(ProbaCollection|array $outputQuantityTable): self
    {
        if ($outputQuantityTable instanceof ProbaCollection) {
            $outputQuantityTable = $outputQuantityTable->toArray();
        }

        $this->outputQuantityTable = $outputQuantityTable;

        return $this;
    }
}
