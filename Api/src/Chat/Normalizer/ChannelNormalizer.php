<?php

namespace Mush\Chat\Normalizer;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;
    private MessageServiceInterface $messageService;

    public function __construct(
        TranslationServiceInterface $translationService,
        MessageServiceInterface $messageService
    ) {
        $this->translationService = $translationService;
        $this->messageService = $messageService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Channel
            && !\in_array('moderation_read', $context['groups'] ?? [], true)
            && $data->isNotTipsChannel();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Channel::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        return $this->normalizeForCurrentPlayer($object, $context);
    }

    private function normalizeForCurrentPlayer(Channel $channel, array $context): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getLanguage();

        if (\array_key_exists('piratedPlayer', $context)) {
            /** @var Player $piratedPlayer */
            $piratedPlayer = $context['piratedPlayer'];
            $piratedPlayerId = $piratedPlayer->getId();
        } else {
            $piratedPlayerId = null;
        }

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
            'description' => $this->translationService->translate($channel->getScope() . '.description', [], 'chat', $language),
            'participants' => $participants,
            'createdAt' => $channel->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'newMessageAllowed' => $this->messageService->canPlayerPostMessage($currentPlayer, $channel),
            'piratedPlayer' => $piratedPlayerId,
            'numberOfNewMessages' => $this->messageService->getNumberOfNewMessagesForPlayer($currentPlayer, $channel),
        ];
    }
}
