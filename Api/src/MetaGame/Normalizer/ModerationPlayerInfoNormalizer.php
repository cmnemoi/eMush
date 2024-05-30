<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ModerationPlayerInfoNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PLAYER_INFO_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private PlayerInfoRepositoryInterface $playerInfoRepository,
        private TokenStorageInterface $tokenStorage,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof PlayerInfo;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = [])
    {
        /** @var PlayerInfo $playerInfo */
        $playerInfo = $object;
        $context[self::ALREADY_CALLED] = true;

        /** @var ?User $requestUser */
        // If user is not logged in, they cannot access player infos
        $requestUser = $this->tokenStorage->getToken()?->getUser();
        if ($requestUser === null) {
            return null;
        }

        // If user is in the same Daedalus as the player they are trying to access, refuse access
        $requestUserPlayerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($requestUser);
        if ($requestUserPlayerInfo?->getDaedalusName() === $playerInfo->getDaedalusName()) {
            return null;
        }

        // Else, return the player info
        return $this->normalizer->normalize($playerInfo, $format, $context);
    }
}
