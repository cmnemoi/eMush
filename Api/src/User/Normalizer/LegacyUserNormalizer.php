<?php

declare(strict_types=1);

namespace Mush\User\Normalizer;

use Mush\User\Entity\LegacyUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LegacyUserNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, string $format = null)
    {
        return $data instanceof LegacyUser;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var LegacyUser $legacyUser */
        $legacyUser = $object;

        return [
            'id' => $legacyUser->getId(),
            'createdAt' => $legacyUser->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $legacyUser->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'userId' => $legacyUser->getUser()->getUserId(),
            'twinoidId' => $legacyUser->getTwinoidProfile()->getTwinoidId(),
            'twinoidUsername' => $legacyUser->getTwinoidProfile()->getTwinoidUsername(),
            'stats' => $legacyUser->getTwinoidProfile()->getStats(),
            'achievements' => $legacyUser->getTwinoidProfile()->getAchievements(),
            'historyHeroes' => $legacyUser->getHistoryHeroes(),
            'historyShips' => $legacyUser->getHistoryShips(),
            'characterLevels' => $legacyUser->getCharacterLevels(),
        ];
    }
}
