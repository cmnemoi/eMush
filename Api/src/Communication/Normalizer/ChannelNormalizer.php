<?php

namespace Mush\Communication\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Communication\Entity\Channel;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\SkillEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Equipment\Entity\GameItem;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
            'participants' => $participants
        ];
    }
}
