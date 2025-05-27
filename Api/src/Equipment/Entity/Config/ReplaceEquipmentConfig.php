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

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $equipmentName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $replacedEquipmentName;

    public function __construct(
        string $name = '',
        string $equipmentName = '',
        string $replacedEquipmentName = '',
    ) {
        $this->name = $name;
        $this->equipmentName = $equipmentName;
        $this->replacedEquipmentName = $replacedEquipmentName;
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

    public function updateFromDto(ReplaceEquipmentConfigDto $dto): self
    {
        $this->name = $dto->name;
        $this->equipmentName = $dto->equipmentName;
        $this->replacedEquipmentName = $dto->replaceEquipmentName;

        return $this;
    }
}
