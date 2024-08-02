<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
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
        private TranslationServiceInterface $translationService
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof PlayerInfo;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): ?array
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
        if ($requestUserPlayerInfo?->getDaedalusId() === $playerInfo->getDaedalusId()) {
            return null;
        }

        // If user is in the same daedalus as the player they are trying to access and the game is not finished, refuse access
        $requestUserPlayerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($requestUser);
        if ($requestUserPlayerInfo?->getDaedalusId() === $playerInfo->getDaedalusId()) {
            return null;
        }

        // If the player is still alive return normalized player
        $player = $playerInfo->getPlayer();
        if ($player !== null) {
            $daedalus = $player->getDaedalus();
            $language = $daedalus->getLanguage();

            $normalisedPlayerInfo = [
                'gameStatus' => $playerInfo->getGameStatus(),
                'isMush' => $player->isMush(),
                'isAlive' => $player->isAlive(),
                'cycleStartedAt' => $daedalus->getCycleStartedAt()?->format('Y-m-d H:i:s'),
                'daedalusDay' => $daedalus->getDay(),
                'daedalusCycle' => $daedalus->getCycle(),
            ];
        } else {
            // Else, return the dead player info
            $closedPlayer = $playerInfo->getClosedPlayer();
            $closedDaedalus = $closedPlayer->getClosedDaedalus();
            $language = $closedDaedalus->getDaedalusInfo()->getLanguage();
            $normalisedPlayerInfo = [
                'gameStatus' => $playerInfo->getGameStatus(),
                'isMush' => $playerInfo->getClosedPlayer()->isMush(),
                'isAlive' => false,
                'cycleStartedAt' => null,
                'daedalusDay' => $closedPlayer->getDay(),
                'daedalusCycle' => $closedPlayer->getCycle(),
            ];
        }

        $normalisedPlayerInfo['id'] = $playerInfo->getId();
        $normalisedPlayerInfo['daedalusId'] = $playerInfo->getDaedalusId();
        $normalisedPlayerInfo['user'] = $this->normalizePlayerUser($playerInfo);
        $normalisedPlayerInfo['character'] = $this->normalizePlayerCharacter($playerInfo, $language);

        return $normalisedPlayerInfo;
    }

    private function normalizePlayerUser(PlayerInfo $player): array
    {
        return [
            'id' => $player->getUser()->getId(),
            'userId' => $player->getUser()->getUserId(),
            'username' => $player->getUser()->getUsername(),
            'isBanned' => $player->getUser()->isBanned(),
        ];
    }

    private function normalizePlayerCharacter(PlayerInfo $player, string $language): array
    {
        $character = $player->getName();

        return [
            'characterName' => $character,
            'characterValue' => $this->translationService->translate($character . '.name', [], 'characters', $language),
        ];
    }
}
