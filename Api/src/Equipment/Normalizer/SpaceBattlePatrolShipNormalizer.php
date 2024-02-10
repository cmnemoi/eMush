<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SpaceBattlePatrolShipNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment && EquipmentEnum::getPatrolShips()->contains($data->getName());
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $object;
        $patrolShipName = $patrolShip->getName();

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        /** @var ChargeStatus $patrolShipCharges */
        $patrolShipCharges = $patrolShip->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        $patrolShipPilot = $patrolShip->getPlace()->getPlayers()->getPlayerAlive()->first();

        if ($patrolShipPilot instanceof Player) {
            $pilotKey = $patrolShipPilot->getName();
        } else {
            $pilotKey = null;
        }

        return [
            'id' => $patrolShip->getId(),
            'key' => $patrolShipName,
            'name' => $this->translationService->translate(
                key: $patrolShipName,
                parameters: [],
                domain: 'equipment',
                language: $patrolShip->getDaedalus()->getLanguage()
            ),
            'armor' => $patrolShipArmor?->getCharge(),
            'charges' => $patrolShipCharges?->getCharge(), // Pasiphae doesn't have charges so do not try to normalize them
            'pilot' => $pilotKey,
            'isBroken' => $patrolShip->hasStatus(EquipmentStatusEnum::BROKEN),
        ];
    }
}
