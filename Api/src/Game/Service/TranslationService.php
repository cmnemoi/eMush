<?php

namespace Mush\Game\Service;

use Mush\Game\Enum\CharacterEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService implements TranslationServiceInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    private static array $conversionArray = [
        'player' => 'character',
        'targetPlayer' => 'character',
        'cause' => 'end_cause',
        'title' => 'status',
        'targetEquipment' => 'equipments',
        'targetItem' => 'items',
        'place' => 'rooms',
    ];

    public function translate(string $key, array $parameters, string $domain): string
    {
        //@TODO include methods getTranslateParameters for other languages than FR
        return $this->translator->trans($key, $this->getFrenchTranslateParameters($parameters), $domain);
    }

    private function getFrenchTranslateParameters(array $parameters): array
    {
        $params = [];
        foreach ($parameters as $key => $element) {
            $params = array_merge($params, $this->getFrenchTranslateParameter($key, $element));
        }

        return $params;
    }

    private function getFrenchTranslateParameter(string $key, string $element): array
    {
        switch ($key) {
            case 'player':
            case 'targetPlayer':
                return $this->getFrenchCharacterTranslateParameter($key, $element);

            case 'targetEquipment':
            case 'targetItem':
                return $this->getFrenchEquipmentTranslateParameter($element, self::$conversionArray[$key]);

            case 'place':
                return [
                    'place' => $this->translator->trans($element . '.name', [], 'rooms'),
                    'loc_prep' => $this->translator->trans($element . '.loc_prep', [], 'rooms'),
                ];

            case 'quantity':
                return [$key => $element];

            default:
                return [$key => $this->translator->trans($element . '.name', [], self::$conversionArray[$key])];
        }
    }

    private function getFrenchEquipmentTranslateParameter(string $element, string $domain): array
    {
        $params = [];
        $params['target'] = $this->translator->trans($element . '.short_name', [], $domain);
        $params['target_gender'] = $this->translator->trans($element . '.genre', [], $domain);
        $params['target_first_letter'] = $this->translator->trans($element . '.first_Letter', [], $domain);
        $params['target_plural'] = $this->translator->trans($element . '.plural_name', [], $domain);

        return $params;
    }

    private function getFrenchCharacterTranslateParameter(string $key, string $element): array
    {
        return [
            $key => $this->translator->trans($element . '.name', [], 'characters'),
            $key . '_gender' => (CharacterEnum::isMale($element) ? 'male' : 'female'),
        ];
    }
}
