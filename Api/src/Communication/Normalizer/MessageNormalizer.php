<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
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
            'child' => $child,
        ];
    }
}
