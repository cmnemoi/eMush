<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChannelNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
                    'value' => $this->translator->trans($character . '.name', [], 'characters'),
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
