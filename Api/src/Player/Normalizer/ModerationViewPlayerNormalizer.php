<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ModerationViewPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Player
               && isset($context['groups']) // only moderators can recover this data
               && \in_array('moderation_view', $context['groups'], true);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = [])
    {
        /** @var Player $player */
        $player = $object;
        $daedalus = $player->getDaedalus();
        $language = $daedalus->getLanguage();

        $context['currentPlayer'] = $player;

        return [
            'id' => $player->getId(),
            'daedalusId' => $daedalus->getId(),
            'user' => $this->normalizePlayerUser($player),
            'character' => $this->normalizePlayerCharacter($player, $language),
            'isMush' => $player->isMush(),
            'isAlive' => $player->isAlive(),
            'cycleStartedAt' => $player->getDaedalus()->getCycleStartedAt()?->format('Y-m-d H:i:s'),
            'daedalusDay' => $player->getDaedalus()->getDay(),
            'daedalusCycle' => $player->getDaedalus()->getCycle(),
        ];
    }

    private function normalizePlayerUser(Player $player): array
    {
        return [
            'id' => $player->getUser()->getId(),
            'userId' => $player->getUser()->getUserId(),
            'username' => $player->getUser()->getUsername(),
            'isBanned' => $player->getUser()->isBanned(),
        ];
    }

    private function normalizePlayerCharacter(Player $player, string $language): array
    {
        $character = $player->getName();

        return [
            'key' => $character,
            'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
            'description' => $this->translationService->translate($character . '.description', [], 'characters', $language),
        ];
    }
}
