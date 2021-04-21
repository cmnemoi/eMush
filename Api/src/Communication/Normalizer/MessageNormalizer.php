<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Game\Enum\CharacterEnum;
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
            $character = CharacterEnum::NERON;
            $parameters = $object->getTranslationParameters();
            if ($parameters) {
                $translatedParameters = $this->translateParameters($parameters);
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

    private function translateParameters(array $parameters): array
    {
        $params = [];
        foreach ($parameters as $key => $element) {
            switch ($key) {
                case 'player':
                    $params['player'] = $this->translator->trans($element . '.name', [], 'characters');
                    break;
                case 'cause':
                    $params['cause'] = $this->translator->trans($element . '.name', [], 'end_cause');
                    break;
                case 'targetEquipment':
                    $domain = 'equipments';

                    $params['target'] = $this->translator->trans($element . '.name', [], $domain);
                    $params['target_gender'] = $this->translator->trans($element . '.genre', [], $domain);
                    break;
                case 'targetItem':
                    $domain = 'items';

                    $params['target'] = $this->translator->trans($element . '.name', [], $domain);
                    $params['target_gender'] = $this->translator->trans($element . '.genre', [], $domain);
                    break;
                case 'title':
                    $params['title'] = $this->translator->trans($element . '.name', [], 'status');
                    break;
                default:
                    $params[$key] = $element;
                    break;
            }
        }

        return $params;
    }
}
