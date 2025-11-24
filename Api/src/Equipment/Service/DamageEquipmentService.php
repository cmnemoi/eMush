<?php

declare(strict_types=1);

namespace Mush\Equipment\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class DamageEquipmentService implements DamageEquipmentServiceInterface
{
    public function __construct(
        private DeleteEquipmentServiceInterface $deleteEquipment,
        private StatusServiceInterface $statusService
    ) {}

    public function execute(
        GameEquipment $gameEquipment,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        match ($gameEquipment->getEquipment()->getBreakableType()) {
            BreakableTypeEnum::BREAKABLE => $this->break($gameEquipment, $visibility, $tags, $time),
            BreakableTypeEnum::DESTROY_ON_BREAK => $this->destroy($gameEquipment, $visibility, $tags, $time),
            default => null,
        };
    }

    private function break(GameEquipment $gameEquipment, string $visibility, array $tags, \DateTime $time)
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $gameEquipment,
            visibility: $visibility,
            tags: $tags,
            time: $time
        );
    }

    private function destroy(GameEquipment $gameEquipment, string $visibility, array $tags, \DateTime $time)
    {
        $this->deleteEquipment->execute(
            gameEquipment: $gameEquipment,
            visibility: $visibility,
            tags: $tags,
            time: $time
        );
    }
}
