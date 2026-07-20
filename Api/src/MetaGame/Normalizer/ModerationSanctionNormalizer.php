<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\MetaGame\Entity\ModerationSanction;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ModerationSanctionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'MODERATION_SANCTION_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly PlayerInfoRepositoryInterface $playerInfoRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ModerationSanction;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ModerationSanction::class => false,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD)
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement, InvalidReturnType
     *
     * @param mixed $object
     */
    public function normalize($object, ?string $format = null, array $context = []): ?array
    {
        /** @var ModerationSanction $moderationSanction */
        $moderationSanction = $object;
        $context[self::ALREADY_CALLED] = true;
        $context['groups'] = array_merge($context['groups'] ?? [], ['moderation_sanction_read']);

        /** @var ?User $requestUser */
        // If user is not logged in, they cannot access player infos
        $requestUser = $this->tokenStorage->getToken()?->getUser();
        if ($requestUser === null) {
            return null;
        }

        // If user is in the same daedalus as the player they are trying to access and the game is not finished, refuse access
        $requestUserPlayerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($requestUser);
        if (
            $requestUserPlayerInfo !== null
            && $this->appNotInDev()
            && $requestUserPlayerInfo->getDaedalusId() === $moderationSanction->getPlayer()?->getDaedalusId()
        ) {
            return null;
        }

        $normalized = $this->normalizer->normalize($moderationSanction, $format, $context);

        if (!\is_array($normalized)) {
            return $normalized;
        }

        $isReport = $moderationSanction->getIsReport();
        $reportedPlayerInfo = $isReport ? $moderationSanction->getPlayer() : null;
        $authorPlayerInfo = $isReport ? $moderationSanction->getAuthorPlayer() : null;

        // Fallback to retrieve the author's player info using the author's user
        // and the reported player info. This is done to handle earlier reports
        // where the author's player info is not directly available.
        if ($authorPlayerInfo === null && $isReport && $reportedPlayerInfo !== null) {
            $authorPlayerInfo = $this->playerInfoRepository->findPlayerInfoInSameGameOrNull(
                $moderationSanction->getAuthor(),
                $reportedPlayerInfo
            );
        }

        $normalized['user'] = [
            'id' => $moderationSanction->getUser()->getUserId(),
            'username' => $moderationSanction->getUser()->getUsername(),
            'playerId' => $reportedPlayerInfo?->getId(),
            'playerName' => $reportedPlayerInfo?->getName(),
        ];
        $normalized['author'] = [
            'id' => $moderationSanction->getAuthor()->getUserId(),
            'username' => $moderationSanction->getAuthor()->getUsername(),
            'playerId' => $authorPlayerInfo?->getId(),
            'playerName' => $authorPlayerInfo?->getName(),
        ];

        return $normalized;
    }

    private function appNotInDev(): bool
    {
        if (!isset($_ENV['APP_ENV'])) {
            throw new \RuntimeException('APP_ENV not set');
        }

        return $_ENV['APP_ENV'] !== 'dev';
    }
}
