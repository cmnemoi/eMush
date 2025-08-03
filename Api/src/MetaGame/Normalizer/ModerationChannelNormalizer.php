<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ModerationChannelNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Channel && \in_array('moderation_read', $context['groups'] ?? [], true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Channel::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        return $this->normalizeForModerators($object);
    }

    private function normalizeForModerators(Channel $channel): array
    {
        $language = $channel->getDaedalusInfo()->getLanguage();
        $participants = [];
        $allTimeParticipants = [];

        // normalize actual participants
        /** @var ChannelPlayer $participant */
        foreach ($channel->getParticipants() as $participant) {
            /** @var \DateTime $joinDate */
            $joinDate = $participant->getCreatedAt();
            $player = $participant->getParticipant();
            $character = $player->getName();
            $participants[] = [
                'id' => $player->getId(),
                'character' => [
                    'key' => $character,
                    'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
                ],
                'joinedAt' => $joinDate->format(\DateTimeInterface::ATOM),
            ];
        }

        // normalize all time participants
        /** @var ChannelPlayer $participant */
        foreach ($channel->getAllTimeParticipants() as $participant) {
            /** @var \DateTime $joinDate */
            $joinDate = $participant->getCreatedAt();
            $player = $participant->getParticipant();
            $character = $player->getName();
            $allTimeParticipants[] = [
                'id' => $player->getId(),
                'character' => [
                    'key' => $character,
                    'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
                ],
                'joinedAt' => $joinDate->format(\DateTimeInterface::ATOM),
            ];
        }

        return [
            'id' => $channel->getId(),
            'scope' => $channel->getScope(),
            'name' => $this->translationService->translate($channel->getScope() . '.name', [], 'chat', $language),
            'participants' => $participants,
            'allTimeParticipants' => $allTimeParticipants,
        ];
    }
}
