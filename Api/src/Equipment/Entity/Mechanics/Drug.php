<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

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

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::DRUG;

        return $mechanics;
    }
}
