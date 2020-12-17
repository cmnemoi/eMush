<?php

namespace Mush\User\Normalizer;

use Mush\User\Entity\User;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof User;
    }

    /**
     * @param User $object
     *
     * @return array
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            'id' => $object->getId(),
            'userId' => $object->getUserId(),
            'username' => $object->getUsername(),
            'currentGame' => $object->getCurrentGame() ? $object->getCurrentGame()->getId() : null,
            'createdAt' => $object->getCreatedAt(),
            'updatedAt' => $object->getUpdatedAt(),
        ];
    }
}
