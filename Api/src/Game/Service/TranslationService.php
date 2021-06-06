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
                return [
                    'player' => $this->translator->trans($element . '.name', [], 'characters'),
                    'character_gender' => (CharacterEnum::isMale($element) ? 'male' : 'female'),
                ];

            case 'targetPlayer':
                return [
                    'target_player' => $this->translator->trans($element . '.name', [], 'characters'),
                    'target_player_gender' => (CharacterEnum::isMale($element) ? 'male' : 'female'),
                ];

            case 'cause':
                return ['cause' => $this->translator->trans($element . '.name', [], 'end_cause')];

            case 'targetEquipment':
                $domain = 'equipments';

                return $this->getFrenchEquipmentTranslateParameter($element, $domain);

            case 'targetItem':
                $domain = 'items';

                return $this->getFrenchEquipmentTranslateParameter($element, $domain);

            case 'title':
                return ['title' => $this->translator->trans($element . '.name', [], 'status')];
                break;

            case 'place':
                return [
                    'place' => $this->translator->trans($element . '.name', [], 'rooms'),
                    'loc_prep' => $this->translator->trans($element . '.loc_prep', [], 'rooms'),
                ];

            default:
                return [$key => $element];
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
}
