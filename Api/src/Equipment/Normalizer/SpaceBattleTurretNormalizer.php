<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SpaceBattleTurretNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment && $data->getName() === EquipmentEnum::TURRET_COMMAND;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var GameEquipment $turret */
        $turret = $object;

        /** @var Place $turretPlace */
        $turretPlace = $turret->getPlace();
        $turretPlaceName = $turretPlace->getName();

        /** @var ChargeStatus $turretCharges */
        $turretCharges = $turret->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        /** @var ArrayCollection<int, Player> $turretOccupiers */
        $turretOccupiers = $turretPlace->getPlayers()->getPlayerAlive();

        return [
            'id' => $turret->getId(),
            'key' => $turretPlaceName,
            'name' => $this->translationService->translate(
                key: $turretPlaceName,
                parameters: [],
                domain: 'room',
                language: $turret->getDaedalus()->getLanguage()
            ),
            'charges' => $turretCharges->getCharge(),
            'occupiers' => !$turretOccupiers->isEmpty() ? $turretOccupiers->map(static fn (Player $player) => $player->getName())->toArray() : [],
            'isBroken' => $turret->hasStatus(EquipmentStatusEnum::BROKEN),
        ];
    }
}
