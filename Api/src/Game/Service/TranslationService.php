<?php

namespace Mush\Game\Service;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService implements TranslationServiceInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function translate(string $key, array $parameters, string $domain, string $language = null): string
    {
        if ($language === null) {
            $language = $this->translator->getLocale();
        }

        $parameters = $this->getTranslateParameters($parameters, $language);

        return $this->translator->trans($key, $parameters, $domain, $language);
    }

    private function getTranslateParameters(array $parameters, string $language): array
    {
        if (array_key_exists($language, $translationMap = LanguageEnum::TRANSLATE_PARAMETERS)) {
            $parameterTranslationMaps = $translationMap[$language];

            foreach ($parameters as $paramKey => $element) {
                $convertedKey = LanguageEnum::convertParameterKeyToTranslationKey($paramKey);

                if (array_key_exists($convertedKey, $parameterTranslationMaps)) {
                    $parameterTranslationMap = $parameterTranslationMaps[$convertedKey];
                } else {
                    $parameterTranslationMap = ['name'];
                }

                $parameters = $this->getTranslateParameter(
                    $paramKey,
                    $element,
                    $parameters,
                    $parameterTranslationMap,
                    $language
                );
            }
        }

        return $parameters;
    }

    private function getTranslateParameter(
        string $initialKey,
        string $parameterTranslationId,
        array $parameters,
        array $parameterTranslationMap,
        string $language
    ): array {
        if (!array_key_exists($initialKey, LanguageEnum::PARAMETER_KEY_TO_DOMAIN)) {
            return $parameters;
        }
        $parameterKey = LanguageEnum::convertParameterKeyToTranslationKey($initialKey);

        $domain = LanguageEnum::PARAMETER_KEY_TO_DOMAIN[$initialKey];

        foreach ($parameterTranslationMap as $additionalInfoKey) {
            $keyInMainString = $this->getKeyInMainTranslation($parameterKey, $additionalInfoKey);

            $translationId = $this->getParameterTranslationId($parameterKey, $additionalInfoKey, $parameterTranslationId);

            $parameters[$keyInMainString] = $this->translator->trans(
                $translationId,
                $parameters,
                $domain,
                $language
            );
        }

        if ($domain === LanguageEnum::CHARACTERS) {
            $parameters[$parameterKey . '_gender'] = (CharacterEnum::isMale($parameterTranslationId) ? 'male' : 'female');
        }

        return $parameters;
    }

    private function getKeyInMainTranslation(string $parameterKey, string $additionalInfoKey): string
    {
        if ($additionalInfoKey === 'name' || $additionalInfoKey === 'short_name') {
            return $parameterKey;
        } elseif (in_array($parameterKey, LanguageEnum::COPROLALIA_PARAMETERS)) {
            return $additionalInfoKey;
        } else {
            return $parameterKey . '_' . $additionalInfoKey;
        }
    }

    private function getParameterTranslationId(
        string $parameterKey,
        string $additionalInfoKey,
        string $parameterTranslationId
    ): string {
        if (in_array($parameterKey, LanguageEnum::COPROLALIA_PARAMETERS)) {
            return $additionalInfoKey;
        } else {
            return $parameterTranslationId . '.' . $additionalInfoKey;
        }
    }
}
