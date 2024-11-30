<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;

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
        foreach ($containerData as [$item, $quantity, $weight]) {
            $itemData = [];
            $itemData['item'] = $item;
            $quantity ? $itemData['quantity'] = $quantity : $itemData['quantity'] = 1;
            $weight ? $itemData['weight'] = $weight : $itemData['weight'] = 1;
            $this->contents[] = $itemData;
        }

        return $this;
    }

    public function getContentWeights(): ProbaCollection
    {
        $probaCollection = new ProbaCollection();

        foreach ($this->contents as [$item, $quantity, $weight]) {
            $probaCollection[$item] = $weight;
        }

        return $probaCollection;
    }

    public function getQuantityOfItemOrThrow(string $searchedItem): int
    {
        foreach ($this->contents as [$item, $quantity, $weight]) {
            if ($this->contents['item'] = $searchedItem) {
                return $this->contents['quantity'];
            }
        }

        return throw new \RuntimeException("Container {$this->getName()} does not contain {$searchedItem}.");
    }
}
