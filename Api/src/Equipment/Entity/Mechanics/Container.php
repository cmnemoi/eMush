<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\ContainerContentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class Container extends EquipmentMechanic
{
    #[ORM\Column(type: 'array', nullable: false)]
    private array $contents;

    public function __construct()
    {
        parent::__construct();
        $this->contents = [];
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::CONTAINER;

        return $mechanics;
    }

    public function setContents(array $containerData): static
    {
        $this->contents = [];

        foreach ($containerData as $itemData) {
            foreach ($itemData as $key => $value) {
                $itemData[$key] = $value;
            }
            $itemData['id'] = $this->generateId($itemData['item'], $itemData['quantity']);
            $this->contents[] = $itemData;
        }

        return $this;
    }

    public function getContentWeights(Player $player): ProbaCollection
    {
        $probaCollection = new ProbaCollection();

        $contents = $this->filterContents($this->contents, $player);

        foreach ($contents as ['id' => $id, 'weight' => $weight]) {
            $probaCollection->setElementProbability($id, $weight);
        }

        return $probaCollection;
    }

    public function getNameOfItemOrThrow(string $searchedId): string
    {
        foreach ($this->contents as ['id' => $id, 'item' => $item]) {
            if ($id === $searchedId) {
                return $item;
            }
        }

        throw new \RuntimeException("Container {$this->getName()} does not contain {$searchedId}.");
    }

    public function getQuantityOfItemOrThrow(string $searchedId): int
    {
        foreach ($this->contents as ['id' => $id, 'quantity' => $quantity]) {
            if ($id === $searchedId) {
                return $quantity;
            }
        }

        throw new \RuntimeException("Container {$this->getName()} does not contain {$searchedId}.");
    }

    private function generateId(string $item, string $quantity): string
    {
        return $item . '_' . $quantity;
    }

    private function filterContents(array $contents, ?Player $player = null): array
    {
        $filteredContents = [];

        foreach ($contents as $item) {
            if (!isset($item['filterType'])) {
                $filteredContents[] = $item;
            } elseif ($item['filterType'] === ContainerContentEnum::FILTER_BY_CHARACTER && $item['filterValue'] === ($player ? $player->getName() : null)) {
                $filteredContents[] = $item;
            }
        }

        return $filteredContents;
    }
}
