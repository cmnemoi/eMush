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
     * @param Channel $channel
     */
    public function normalize($channel, string $format = null, array $context = []): array
    {
        $participants = [];
        /** @var Player $participant */
        foreach ($channel->getParticipants() as $participant) {
            $participants[] = [
                'id' => $participant->getId(),
                'character' => [
                    'key' => $participant->getPerson(),
                    'value' => $this->translator->trans($participant->getPerson() . '.name', [], 'characters'),
                ],
            ];
        }

        return [
            'id' => $channel->getId(),
            'scope' => $channel->getScope(),
            'participants' => $participants,
        ];
    }
}
