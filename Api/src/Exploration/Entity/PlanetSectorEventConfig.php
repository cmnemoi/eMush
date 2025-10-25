<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Exploration\ConfigData\PlanetSectorEventConfigDto;
use Mush\Exploration\Enum\PlanetSectorEventTagEnum;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
class PlanetSectorEventConfig extends AbstractEventConfig
{
    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $outputQuantity = [];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $outputTable = [];

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => PlanetSectorEventTagEnum::NEUTRAL])]
    private string $tag = PlanetSectorEventTagEnum::NEUTRAL;

    public function getOutputQuantity(): ProbaCollection
    {
        return new ProbaCollection($this->outputQuantity);
    }

    public function setOutputQuantity(array|ProbaCollection $outputQuantity): self
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

    public function setOutputTable(array|ProbaCollection $outputTable): self
    {
        if ($outputTable instanceof ProbaCollection) {
            $outputTable = $outputTable->toArray();
        }

        $this->outputTable = $outputTable;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function isPositive(): bool
    {
        return $this->tag === PlanetSectorEventTagEnum::POSITIVE;
    }

    public function isNegative(): bool
    {
        return $this->tag === PlanetSectorEventTagEnum::NEGATIVE;
    }

    public static function fromDto(PlanetSectorEventConfigDto $dto): self
    {
        $config = new self();
        $config->updateFromDto($dto);

        return $config;
    }

    public function updateFromDto(PlanetSectorEventConfigDto $dto): static
    {
        // automatically setting values through reflection to avoid forgetting some
        // when adding new fields
        $dtoProperties = (new \ReflectionClass($dto))->getProperties();

        foreach ($dtoProperties as $property) {
            $propertyName = $property->getName();
            $setterMethod = 'set' . ucfirst($propertyName);

            if (!method_exists($this, $setterMethod)) {
                throw new \Exception("Setter method {$setterMethod} not found for property {$propertyName}");
            }

            $this->{$setterMethod}($property->getValue($dto));
        }

        return $this;
    }
}
