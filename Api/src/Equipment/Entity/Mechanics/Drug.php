<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    normalizationContext: ['groups' => ['drug_read']],
    denormalizationContext: ['groups' => ['drug_write']],
    paginationItemsPerPage: 25,
    operations: [
        new GetCollection(filters: ['default.search_filter', 'default.order_filter']),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Get(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
    ],
)]
class Drug extends Ration
{
    public function __construct()
    {
        parent::__construct();
        $this->isPerishable = false;
    }

    #[Groups(['drug_read'])]
    public function getId(): int
    {
        return parent::getId();
    }

    #[Groups(['drug_read', 'drug_write'])]
    public function getName(): string
    {
        return parent::getName();
    }

    #[Groups(['drug_read', 'drug_write'])]
    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::DRUG;

        return $mechanics;
    }

    #[Groups(['drug_read', 'drug_write'])]
    public function getActions(): Collection
    {
        return parent::getActions();
    }
}
