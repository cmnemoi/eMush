<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Dto\SpawnEquipmentConfigDto;
use Mush\Project\Entity\ProjectConfig;

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

    #[ORM\ManyToOne(targetEntity: ProjectConfig::class, inversedBy: 'spawnEquipmentConfigs')]
    private ProjectConfig $projectConfig;

    public function __construct(
        string $name = '',
        string $equipmentName = '',
        string $placeName = '',
        int $quantity = 1,
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

    public function updateFromDto(SpawnEquipmentConfigDto $dto): static
    {
        $this->name = $dto->name;
        $this->equipmentName = $dto->equipmentName;
        $this->placeName = $dto->placeName;
        $this->quantity = $dto->quantity;

        return $this;
    }
}
