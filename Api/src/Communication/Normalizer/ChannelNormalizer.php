<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Services\MessageServiceInterface;
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

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Channel;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getLanguage();

        if (key_exists('piratedPlayer', $context)) {
            /** @var Player $piratedPlayer */
            $piratedPlayer = $context['piratedPlayer'];
            $piratedPlayerId = $piratedPlayer->getId();
        } else {
            $piratedPlayerId = null;
        }

        $participants = [];
        /** @var ChannelPlayer $participant */
        foreach ($object->getParticipants() as $participant) {
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
            'id' => $object->getId(),
            'scope' => $object->getScope(),
            'name' => $this->translationService->translate($object->getScope() . '.name', [], 'chat', $language),
            'description' => $this->translationService->translate($object->getScope() . '.description', [], 'chat', $language),
            'participants' => $participants,
            'createdAt' => $object->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'newMessageAllowed' => $this->messageService->canPlayerPostMessage($currentPlayer, $object),
            'piratedPlayer' => $piratedPlayerId,
        ];
    }
}
