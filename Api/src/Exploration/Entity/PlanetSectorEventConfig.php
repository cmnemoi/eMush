<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
class PlanetSectorEventConfig extends AbstractEventConfig
{
    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $outputQuantity = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $outputTable = [];

    public function getOutputQuantity(): ProbaCollection
    {
        return new ProbaCollection($this->outputQuantity);
    }

    public function setOutputQuantity(ProbaCollection|array $outputQuantity): self
    {
        if ($outputQuantity instanceof ProbaCollection) {
            $outputQuantity = $outputQuantity->toArray();
        }

        $this->outputQuantity = $outputQuantity;

        return $this;
    }

    public function getOutputTable(): ProbaCollection
    {
        return new ProbaCollection($this->outputTable);
    }

    public function setOutputTable(ProbaCollection|array $outputTable): self
    {
        if ($outputTable instanceof ProbaCollection) {
            $outputTable = $outputTable->toArray();
        }

        $this->outputTable = $outputTable;

        return $this;
    }
}
