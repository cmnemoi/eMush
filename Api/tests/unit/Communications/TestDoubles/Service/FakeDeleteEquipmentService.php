<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;

final class FakeDeleteEquipmentService implements DeleteEquipmentServiceInterface
{
    public function execute(
        GameEquipment $gameEquipment,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ): void {
        $holder = $gameEquipment->getHolder();
        $holder->removeEquipment($gameEquipment);
    }
}
