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

    public function getTranslateParameters(array $parameters): array
    {
        $params = [];
        foreach ($parameters as $key => $element) {
            $params = array_merge($params, $this->getTranslateParameter($key, $element));
        }

        return $params;
    }

    private function getTranslateParameter(string $key, string $element): array
    {
        $params = [];
        switch ($key) {
            case 'player':
                $params['player'] = $this->translator->trans($element . '.name', [], 'characters');
                $params['character_gender'] = (CharacterEnum::isMale($element) ? 'male' : 'female');
                break;
            case 'cause':
                $params['cause'] = $this->translator->trans($element . '.name', [], 'end_cause');
                break;
            case 'targetEquipment':
                $domain = 'equipments';

                $params['target'] = $this->translator->trans($element . '.short_name', [], $domain);
                $params['target_gender'] = $this->translator->trans($element . '.genre', [], $domain);
                $params['target_first_letter'] = $this->translator->trans($element . '.first_Letter', [], $domain);
                $params['target_plural'] = $this->translator->trans($element . '.plural_name', [], $domain);
                break;

            case 'targetItem':
                $domain = 'items';

                $params['target'] = $this->translator->trans($element . '.short_name', [], $domain);
                $params['target_gender'] = $this->translator->trans($element . '.genre', [], $domain);
                $params['target_first_letter'] = $this->translator->trans($element . '.first_Letter', [], $domain);
                $params['target_plural'] = $this->translator->trans($element . '.plural_name', [], $domain);
                break;

            case 'targetPlayer':
                $params['target_player'] = $this->translator->trans($element . '.name', [], 'characters');
                $params['target_player_gender'] = (CharacterEnum::isMale($element) ? 'male' : 'female');
                break;

            case 'title':
                $params['title'] = $this->translator->trans($element . '.name', [], 'status');
                break;

            default:
                $params[$key] = $element;
                break;
        }

        return $params;
    }
}
