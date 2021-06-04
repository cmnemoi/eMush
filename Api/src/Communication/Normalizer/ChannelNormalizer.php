<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ChannelNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
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
        $participants = [];
        /** @var ChannelPlayer $participant */
        foreach ($object->getParticipants() as $participant) {
            $player = $participant->getParticipant();
            $character = $player->getCharacterConfig()->getName();
            $participants[] = [
                'id' => $player->getId(),
                'character' => [
                    'key' => $character,
                    'value' => $this->translationService->translate($character . '.name', [], 'characters'),
                ],
                'joinedAt' => $participant->getCreatedAt()->format(\DateTime::ATOM),
            ];
        }

        return [
            'id' => $object->getId(),
            'scope' => $object->getScope(),
            'participants' => $participants,
            'createdAt' => $object->getCreatedAt()->format(\DateTime::ATOM),
        ];
    }
}
