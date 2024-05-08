<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\SpawnEquipmentConfigDto;

#[ORM\Entity]
class SpawnEquipmentConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $equipmentName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $placeName;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $replacedEquipment = null;

    public function __construct(
        string $name = '',
        string $equipmentName = '',
        string $placeName = '',
        int $quantity = 1,
        ?string $replacedEquipment = null,
    ) {
        $this->name = $name;
        $this->equipmentName = $equipmentName;
        $this->placeName = $placeName;
        $this->quantity = $quantity;
        $this->replacedEquipment = $replacedEquipment;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEquipmentName(): string
    {
        return $this->equipmentName;
    }

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReplacedEquipment(): ?string
    {
        return $this->replacedEquipment;
    }

    public function updateFromDto(SpawnEquipmentConfigDto $dto): static
    {
        $this->name = $dto->name;
        $this->equipmentName = $dto->equipmentName;
        $this->placeName = $dto->placeName;
        $this->quantity = $dto->quantity;
        $this->replacedEquipment = $dto->replaceEquipment;

        return $this;
    }
}
