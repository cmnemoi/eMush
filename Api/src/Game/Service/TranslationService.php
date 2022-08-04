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
        'character' => 'character',
        'target_character' => 'character',
        'cause' => 'end_cause',
        'title' => 'status',
        'target_equipment' => 'equipments',
        'equipment' => 'equipments',
        'target_item' => 'items',
        'item' => 'items',
        'disease' => 'disease',
        'place' => 'rooms',
    ];

    public function translate(string $key, array $parameters, string $domain): string
    {
        // @TODO include methods getTranslateParameters for other languages than FR
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
        return match ($key) {
            'character', 'target_character' => $this->getFrenchCharacterTranslateParameter($key, $element),
            'target_equipment', 'target_item', 'equipment', 'item' => $this->getFrenchEquipmentTranslateParameter($element, $key),
            'place' => [
                'place' => $this->translator->trans($element . '.name', [], 'rooms'),
                'loc_prep' => $this->translator->trans($element . '.loc_prep', [], 'rooms'),
            ],
            'cause', 'title', 'disease' => [$key => $this->translator->trans($element . '.name', [], self::$conversionArray[$key])],
            default => [$key => $element],
        };
    }

    private function getFrenchEquipmentTranslateParameter(string $element, string $key): array
    {
        $domain = self::$conversionArray[$key];

        if ($key === 'target_item') {
            $key = 'target_equipment';
        } elseif ($key === 'item') {
            $key = 'equipment';
        }

        $params = [];
        $params[$key] = $this->translator->trans($element . '.short_name', [], $domain);
        $params[$key . '_gender'] = $this->translator->trans($element . '.genre', [], $domain);
        $params[$key . '_first_letter'] = $this->translator->trans($element . '.first_letter', [], $domain);
        $params[$key . '_plural'] = $this->translator->trans($element . '.plural_name', [], $domain);

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
