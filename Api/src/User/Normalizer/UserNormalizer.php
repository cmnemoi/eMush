<?php

namespace Mush\User\Normalizer;

use Mush\User\Entity\User;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var User $user */
        $user = $object;

        if (($playerInfo = $user->getPlayerInfo()) !== null &&
            ($player = $playerInfo->getPlayer()) !== null
        ) {
            $currentPlayer = $player->getId();
        } else {
            $currentPlayer = null;
        }

        return [
            'id' => $user->getId(),
            'userId' => $user->getUserId(),
            'username' => $user->getUsername(),
            'playerInfo' => $currentPlayer,
            'roles' => $user->getRoles(),
        ];
    }
}
