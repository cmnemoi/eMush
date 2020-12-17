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
     * @param Message $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $child = [];

        /** @var Message $children */
        foreach ($object->getChild() as $children) {
            $child[] = $this->normalize($children, $format, $context);
        }

        return [
            'id' => $object->getId(),
            'character' => [
                'key' => $object->getAuthor()->getPerson(),
                'value' => $this->translator->trans($object->getAuthor()->getPerson() . '.name', [], 'characters'),
            ],
            'message' => $object->getMessage(),
            'createdAt' => $object->getCreatedAt()->format(\DateTime::ATOM),
            'child' => $child,
        ];
    }
}
