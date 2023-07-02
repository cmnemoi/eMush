<?php

declare(strict_types=1);

namespace Mush\Hunter\Normalizer;

use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class HunterNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Hunter && !$data->isInPool();
    }
    
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {   
        /** @var Hunter $hunter */
        $hunter = $object;
        /** @var ChargeStatus $hunterCharges */
        $hunterCharges = $hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
        $hunterName = $hunter->getName();
        $isHunterAnAsteroid = $hunterName === HunterEnum::ASTEROID;

        return [
            'id' => $hunter->getId(),
            'name' => $hunterName,
            'health' => $hunter->getHealth(),
            'charges' => $isHunterAnAsteroid ? $hunterCharges->getCharge() : null,
        ];
    }
}