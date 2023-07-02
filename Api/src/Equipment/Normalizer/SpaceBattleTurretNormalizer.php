<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SpaceBattleTurretNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment && $data->getName() === EquipmentEnum::TURRET_COMMAND;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var GameEquipment $turret */
        $turret = $object;

        /** @var ChargeStatus $turretCharges */
        $turretCharges = $turret->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        /** @var ArrayCollection<int, Player> $turretOccupiers */
        $turretOccupiers = $turret->getPlace()->getPlayers()->getPlayerAlive();

        return [
            'id' => $turret->getId(),
            'charges' => $turretCharges->getCharge(),
            'occupiers' => !$turretOccupiers->isEmpty() ? $turretOccupiers->map(fn (Player $player) => $player->getName())->toArray() : null,
        ];
    }
}
