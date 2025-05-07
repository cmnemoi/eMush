<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpaceBattlePatrolShipNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SpaceShip && EquipmentEnum::getPatrolShips()->contains($data->getName());
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            SpaceShip::class => false,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var SpaceShip $patrolShip */
        $patrolShip = $this->patrolShip($object);

        $patrolShipPlace = $patrolShip->getPlace();
        $patrolShipName = $patrolShip->getPatrolShipName();
        $patrolShipArmor = $patrolShip->getChargeStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        $patrolShipCharges = $patrolShip->getChargeStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $patrolShipPilot = $patrolShipPlace->getAlivePlayers()->first() ?: null;
        $humanPilotKey = $patrolShipPilot?->getName();

        /** @var ?Drone $pilotDrone */
        $pilotDrone = $patrolShipPlace->getEquipmentByName(ItemEnum::SUPPORT_DRONE);

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
            'charges' => $patrolShipCharges?->getCharge(),
            'pilot' => $humanPilotKey,
            'drone' => $pilotDrone?->isPilot() ?? false,
            'isBroken' => $patrolShip->hasStatus(EquipmentStatusEnum::BROKEN),
        ];
    }

    private function patrolShip(mixed $object): GameEquipment
    {
        return $object instanceof GameEquipment ? $object : throw new \RuntimeException('This normalizer only supports GameEquipment');
    }
}
