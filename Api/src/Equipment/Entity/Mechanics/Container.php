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
        /*foreach ($containerData as ['item' => $item, 'quantity' => $quantity, 'weight' => $weight]) {
            $itemData = [];
            $itemData['item'] = $item;
            $itemData['quantity'] = $quantity;
            $itemData['weight'] = $weight;
            $itemData['filter'] = $filter? $filter : null;
            $this->contents[] = $itemData;
        }*/
        foreach ($containerData as $itemData) {
            foreach ($itemData as $key => $value) {
                $itemData[$key] = $value;
            }
            $this->contents[] = $itemData;
        }

        return $this;
    }

    public function getContentWeights(?Player $player): ProbaCollection
    {
        $probaCollection = new ProbaCollection();

        $contents = $this->FilterContents($this->contents, $player);

        foreach ($contents as ['item' => $item, 'weight' => $weight]) {
            $probaCollection->setElementProbability($item, $weight);
        }

        return $probaCollection;
    }

    public function getQuantityOfItemOrThrow(string $searchedItem): int
    {
        foreach ($this->contents as ['item' => $item, 'quantity' => $quantity]) {
            if ($item === $searchedItem) {
                return $quantity;
            }
        }

        throw new \RuntimeException("Container {$this->getName()} does not contain {$searchedItem}.");
    }

    private function FilterContents(array $contents, ?Player $player = null): array
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
