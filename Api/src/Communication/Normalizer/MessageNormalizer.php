<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private TranslationServiceInterface $translationService;

    public function __construct(TranslatorInterface $translator, TranslationServiceInterface $translationService)
    {
        $this->translator = $translator;
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

            $parameters = $object->getTranslationParameters();
            if ($parameters) {
                $translatedParameters = $this->translationService->getTranslateParameters($parameters);
            } else {
                $translatedParameters = [];
            }

            $message = $this->translator->trans(
                $object->getMessage(),
                $translatedParameters,
                'neron'
            );
        }

        return [
            'id' => $object->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translator->trans($character . '.name', [], 'characters'),
            ],
            'message' => $message,
            'createdAt' => $object->getCreatedAt()->format(\DateTime::ATOM),
            'child' => $child,
        ];
    }
}
