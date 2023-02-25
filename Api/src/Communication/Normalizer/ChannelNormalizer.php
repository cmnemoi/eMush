<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ChannelNormalizer implements ContextAwareNormalizerInterface
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

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getGameConfig()->getLanguage();

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
            $player = $participant->getParticipant();
            $character = $player->getName();
            $participants[] = [
                'id' => $player->getId(),
                'character' => [
                    'key' => $character,
                    'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
                ],
                'joinedAt' => $participant->getCreatedAt()->format(\DateTime::ATOM),
            ];
        }

        return [
            'id' => $object->getId(),
            'scope' => $object->getScope(),
            'name' => $this->translationService->translate($object->getScope() . '.name', [], 'chat', $language),
            'description' => $this->translationService->translate($object->getScope() . '.description', [], 'chat', $language),
            'participants' => $participants,
            'createdAt' => $object->getCreatedAt()->format(\DateTime::ATOM),
            'newMessageAllowed' => $this->messageService->canPlayerPostMessage($currentPlayer, $object),
            'piratedPlayer' => $piratedPlayerId,
        ];
    }
}
