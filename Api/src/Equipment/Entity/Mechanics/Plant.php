<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    paginationItemsPerPage: 25,
    normalizationContext: ['groups' => ['plant_read']],
    denormalizationContext: ['groups' => ['plant_write']],
    operations: [
        new GetCollection(filters: ['default.search_filter', 'default.order_filter']),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Get(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
    ],
)]
#[ORM\Entity]
class Plant extends EquipmentMechanic
{
    #[Groups(['plant_read', 'plant_write'])]
    #[ORM\Column(type: 'string', nullable: false)]
    private string $fruitName;

    #[Groups(['plant_read', 'plant_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $maturationTime;

    #[Groups(['plant_read', 'plant_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $oxygen;

    public function __construct()
    {
        parent::__construct();
        $this->maturationTime = [];
        $this->oxygen = [];
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::PLANT;

        return $mechanics;
    }

    public function getFruitName(): string
    {
        return $this->fruitName;
    }

    public function setFruitName(string $fruitName): self
    {
        $this->fruitName = $fruitName;

        return $this;
    }

    public function getMaturationTime(): ProbaCollection
    {
        return new ProbaCollection($this->maturationTime);
    }

    public function setMaturationTime(array $maturationTime): self
    {
        $this->maturationTime = $maturationTime;

        return $this;
    }

    public function getOxygen(): ProbaCollection
    {
        return new ProbaCollection($this->oxygen);
    }

    public function setOxygen(array $oxygen): self
    {
        $this->oxygen = $oxygen;

        return $this;
    }
}
