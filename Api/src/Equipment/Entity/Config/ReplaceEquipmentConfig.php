<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\ReplaceEquipmentConfigDto;

#[ORM\Entity]
class ReplaceEquipmentConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $equipmentName;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $replacedEquipmentName;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $placeName;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $quantity;

    public function __construct(
        string $name = '',
        string $equipmentName = '',
        string $replacedEquipmentName = '',
        string $placeName = '',
        int $quantity = 1,
    ) {
        $this->name = $name;
        $this->equipmentName = $equipmentName;
        $this->replacedEquipmentName = $replacedEquipmentName;
        $this->placeName = $placeName;
        $this->quantity = $quantity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEquipmentName(): string
    {
        return $this->equipmentName;
    }

    public function getReplacedEquipmentName(): string
    {
        return $this->replacedEquipmentName;
    }

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function shouldReplaceInSpecificPlace(): bool
    {
        return $this->placeName !== '';
    }

    public function updateFromDto(ReplaceEquipmentConfigDto $dto): self
    {
        $this->name = $dto->name;
        $this->equipmentName = $dto->equipmentName;
        $this->replacedEquipmentName = $dto->replaceEquipmentName;
        $this->placeName = $dto->placeName;
        $this->quantity = $dto->quantity;

        return $this;
    }
}
