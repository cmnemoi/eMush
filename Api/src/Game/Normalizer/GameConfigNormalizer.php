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
            'name' => $object->getName(),
            'max_action_point' => $object->getMaxActionPoint(),
            'max_movement_point' => $object->getMaxMovementPoint(),
            'max_health_point' => $object->getMaxHealthPoint(),
            'max_moral_point' => $object->getMaxMoralPoint(),
            'max_player' => $object->getMaxPlayer(),
            'max_item_inventory' => $object->getMaxItemInInventory(),
            'max_number_private_channel' => $object->getMaxNumberPrivateChannel(),
            'language' => $object->getLanguage(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof GameConfig;
    }
}