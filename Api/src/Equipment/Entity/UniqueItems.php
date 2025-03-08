<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UniqueItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'array', options: ['default' => 'a:0:{}'])]
    private array $startingBlueprints = [];

    public function getStartingBlueprints(): array
    {
        return $this->startingBlueprints;
    }

    public function makeStartingBlueprintsUnique(array $blueprintNames): void
    {
        foreach ($blueprintNames as $blueprintName) {
            $this->addUniqueBlueprintByName($blueprintName);
        }
    }

    private function addUniqueBlueprintByName(string $gameItemName): void
    {
        if (!\in_array($gameItemName, $this->startingBlueprints, true)) {
            $this->startingBlueprints[] = $gameItemName;
        }
    }
}
