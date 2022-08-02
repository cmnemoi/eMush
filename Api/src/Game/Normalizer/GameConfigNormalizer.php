<?php

namespace Mush\Game\Normalizer;

use Mush\Game\Entity\GameConfig;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GameConfigNormalizer implements NormalizerInterface
{
    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'nbMush' => $object->getNbMush(),
            'cyclePerGameDay' => $object->getCyclePerGameDay(),
            'cycleLength' => $object->getCycleLength(),
            'timeZone' => $object->getTimeZone(),
            'maxNumberPrivateChannel' => $object->getMaxNumberPrivateChannel(),
            'language' => $object->getLanguage(),
            'initHealthPoint' => $object->getInitHealthPoint(),
            'maxHealthPoint' => $object->getMaxHealthPoint(),
            'initMoralPoint' => $object->getInitMoralPoint(),
            'maxMoralPoint' => $object->getMaxMoralPoint(),
            'initSatiety' => $object->getInitSatiety(),
            'initActionPoint' => $object->getInitActionPoint(),
            'maxActionPoint' => $object->getMaxActionPoint(),
            'initMovementPoint' => $object->getInitMovementPoint(),
            'maxMovementPoint' => $object->getMaxMovementPoint(),
            'maxItemInInventory' => $object->getMaxItemInInventory(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof GameConfig;
    }
}
