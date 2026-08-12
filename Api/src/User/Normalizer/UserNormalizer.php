<?php

declare(strict_types=1);

namespace Mush\User\Normalizer;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        PlayerInfoRepository $playerInfoRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
        $this->playerInfoRepository = $playerInfoRepository;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var ?User $requestUser */
        $requestUser = $this->tokenStorage->getToken()?->getUser();
        if ($requestUser === null) {
            return [];
        }

        /** @var User $user */
        $user = $object;

        $requestedByUser = $requestUser === $user;
        $requestedByModerator = $requestUser->isModerator();

        if ($user->isInGame()) {
            /** @var PlayerInfo $playerInfo */
            $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

            /** @var Player $player */
            $player = $playerInfo->getPlayer();

            $currentPlayer = $player->getId();
        } else {
            $currentPlayer = null;
        }

        $normalizedUser = [
            'id' => $user->getId(),
            'userId' => $user->getUserId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($requestedByUser || $requestedByModerator) {
            $normalizedUser['playerInfo'] = $currentPlayer;
            $normalizedUser['isBanned'] = $user->isBanned();
            $normalizedUser['hasAcceptedRules'] = $user->hasAcceptedRules();
        }

        if ($requestedByUser) {
            $normalizedUser['likeToBeMush'] = $user->getLikeToBeMush();
        }

        return $normalizedUser;
    }
}
