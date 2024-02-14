<?php

namespace Mush\MetaGame\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ModerationChannelNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Channel && in_array('moderation_read', $context['groups'] ?? []);
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        return $this->normalizeForModerators($object);
    }

    private function normalizeForModerators(Channel $channel): array
    {
        $language = LanguageEnum::FRENCH;
        $participants = [];
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

        return [
            'id' => $channel->getId(),
            'scope' => $channel->getScope(),
            'name' => $this->translationService->translate($channel->getScope() . '.name', [], 'chat', $language),
            'participants' => $participants,
        ];
    }
}
