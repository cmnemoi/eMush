<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class MessageNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Message;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $child = [];

        /** @var Message $children */
        foreach ($object->getChild() as $children) {
            $child[] = $this->normalize($children, $format, $context);
        }

        if ($object->getAuthor()) {
            $character = $object->getAuthor()->getCharacterConfig()->getName();
            $message = $object->getMessage();
        } else {
            $character = null;
            if ($object->getNeron()) {
                $character = CharacterEnum::NERON;
            }

            $message = $this->translationService->translate(
                $object->getMessage(),
                $object->getTranslationParameters(),
                'neron'
            );
        }

        return [
            'id' => $object->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate("{$character}.name", [], 'characters'),
            ],
            'message' => $message,
            'createdAt' => $object->getCreatedAt()->format(\DateTime::ATOM),
            'child' => $child,
        ];
    }
}
