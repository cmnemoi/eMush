<?php

namespace Mush\User\Normalizer;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        PlayerInfoRepository $playerInfoRepository
    ) {
        $this->playerInfoRepository = $playerInfoRepository;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var User $user */
        $user = $object;

        if ($user->isInGame()) {
            /** @var PlayerInfo $playerInfo */
            $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);
            /** @var Player $player */
            $player = $playerInfo->getPlayer();

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
            'legacyUser' => $this->normalizer->normalize($user->getLegacyUser(), $format, $context),
        ];
    }
}
