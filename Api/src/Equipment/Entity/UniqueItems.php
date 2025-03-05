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

    #[ORM\Column(type: 'array', nullable: true, options: ['default' => '[]'])]
    private array $uniqueItemNames = [];

    public function getUniqueItemNames(): array
    {
        $count = \count($this->uniqueItemNames);

        return $this->uniqueItemNames;
    }

    public function addUniqueMageBookByGameEquipment(GameEquipment $mageBook): void
    {
        $skillName = $mageBook->getBookMechanicOrThrow()->getSkill();
        $this->addUniqueItemByName($mageBook->getName() . '_' . $skillName->value);
    }

    private function addUniqueItemByName(string $gameItemName): void
    {
        if (!\in_array($gameItemName, $this->uniqueItemNames, true)) {
            $this->uniqueItemNames[] = $gameItemName;
            $count = \count($this->uniqueItemNames);
        }
    }
}
