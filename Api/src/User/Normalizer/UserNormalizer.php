<?php

namespace Mush\User\Normalizer;

use Mush\User\Entity\User;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'id' => $object->getId(),
            'userId' => $object->getUserId(),
            'username' => $object->getUsername(),
            'currentGame' => $object->getCurrentGame() ? $object->getCurrentGame()->getId() : null,
            'roles' => $object->getRoles(),
            'createdAt' => $object->getCreatedAt(),
            'updatedAt' => $object->getUpdatedAt(),
        ];
    }
}
