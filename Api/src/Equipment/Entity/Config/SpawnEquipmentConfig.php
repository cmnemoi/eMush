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

    #[ORM\Version]
    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 1])]
    private int $version = 1;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $equipmentName;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $placeName;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $quantity;

    public function __construct(
        string $name = '',
        string $equipmentName = '',
        string $placeName = '',
        int $quantity = 0,
    ) {
        $this->name = $name;
        $this->equipmentName = $equipmentName;
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

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function updateFromDto(SpawnEquipmentConfigDto $dto): self
    {
        $this->name = $dto->name;
        $this->equipmentName = $dto->equipmentName;
        $this->placeName = $dto->placeName;
        $this->quantity = $dto->quantity;

        return $this;
    }
}
