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

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $tags = [PlanetSectorEventTagEnum::NEUTRAL];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $fightStrength = 0;

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

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->tags, true);
    }

    public function isPositive(): bool
    {
        return $this->hasTag(PlanetSectorEventTagEnum::POSITIVE);
    }

    public function isNegative(): bool
    {
        return $this->hasTag(PlanetSectorEventTagEnum::NEGATIVE);
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

    public function getFightStrength(): int
    {
        return $this->fightStrength;
    }

    public function setFightStrength(int $fightStrength): static
    {
        $this->fightStrength = $fightStrength;

        return $this;
    }
}
