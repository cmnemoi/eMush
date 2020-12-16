<?php

namespace Mush\Communication\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
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

class MessageNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Message;
    }

    /**
     * @param Message $message
     */
    public function normalize($message, string $format = null, array $context = []): array
    {
        $child = [];

        /** @var Message $children */
        foreach ($message->getChild() as $children) {
            $child[] = $this->normalize($children, $format, $context);
        }

        return [
            'id' => $message->getId(),
            'character' => [
                'key' => $message->getAuthor()->getPerson(),
                'value' => $this->translator->trans($message->getAuthor()->getPerson() . '.name', [], 'characters'),
            ],
            'message' => $message->getMessage(),
            'createdAt' => $message->getCreatedAt()->format(\DateTime::ATOM),
            'child' => $child
        ];
    }
}
