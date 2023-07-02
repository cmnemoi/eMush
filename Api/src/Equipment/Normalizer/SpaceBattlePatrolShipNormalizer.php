<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SpaceBattlePatrolShipNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment && EquipmentEnum::getPatrolShips()->contains($data->getName());
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $object;

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        /** @var ChargeStatus $patrolShipCharges */
        $patrolShipCharges = $patrolShip->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        /** @var Player $patrolShipPilot */
        $patrolShipPilot = $patrolShip->getPlace()->getPlayers()->getPlayerAlive()->first();

        return [
            'id' => $patrolShip->getId(),
            'name' => $patrolShip->getName(),
            'armor' => $patrolShipArmor->getCharge(),
            'charges' => $patrolShipCharges ? $patrolShipCharges->getCharge() : null,
            'pilot' => $patrolShipPilot->getName(),
        ];
    }
}
