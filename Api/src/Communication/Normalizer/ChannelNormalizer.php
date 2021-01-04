<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Player\Entity\Player;
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
        /** @var Player $participant */
        foreach ($object->getParticipants() as $participant) {
            $character = $participant->getCharacterConfig()->getName();
            $participants[] = [
                'id' => $participant->getId(),
                'character' => [
                    'key' => $character,
                    'value' => $this->translator->trans($character . '.name', [], 'characters'),
                ],
            ];
        }

        return [
            'id' => $object->getId(),
            'scope' => $object->getScope(),
            'participants' => $participants,
        ];
    }
}
