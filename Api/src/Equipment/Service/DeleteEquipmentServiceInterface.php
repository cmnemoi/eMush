<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;

interface DeleteEquipmentServiceInterface
{
    public function execute(
        GameEquipment $gameEquipment,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void;
}
