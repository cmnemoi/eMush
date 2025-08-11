<?php

namespace Mush\Game\Service;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService implements TranslationServiceInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
        private readonly LoggerInterface $logger
    ) {
        $this->translator = $translator;
    }

    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        if ($language === null) {
            $language = $this->translator->getLocale();
        }

        $parameters = $this->getTranslateParameters($parameters, $language);

        try {
            return $this->translator->trans($key, $parameters, $domain, $language);
        } catch (\Exception $e) {
            $this->logger->error('Error translating key: ' . $key, [
                'parameters' => $parameters,
                'domain' => $domain,
                'language' => $language,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function getTranslateParameters(array $parameters, string $language): array
    {
        if (\array_key_exists($language, $translationMap = LanguageEnum::TRANSLATE_PARAMETERS)) {
            $parameterTranslationMaps = $translationMap[$language];

            foreach ($parameters as $paramKey => $element) {
                // We may sometimes have null log parameters (exemple : PlanetName parts) : we can just skip their translations
                if ($element === null) {
                    continue;
                }
                $convertedKey = LanguageEnum::convertParameterKeyToTranslationKey($paramKey);

                if (\array_key_exists($convertedKey, $parameterTranslationMaps)) {
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
        if (!\array_key_exists($initialKey, LanguageEnum::PARAMETER_KEY_TO_DOMAIN)) {
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
            $parameters[$parameterKey . '_gender'] = CharacterEnum::exists($parameterTranslationId) ? CharacterEnum::gender($parameterTranslationId) : 'male';
            $parameters[$parameterKey . '_icon'] = ':' . $parameterTranslationId . ':';
        }

        return $parameters;
    }

    private function getKeyInMainTranslation(string $parameterKey, string $additionalInfoKey): string
    {
        if ($additionalInfoKey === 'name' || $additionalInfoKey === 'short_name') {
            return $parameterKey;
        }
        if (\in_array($parameterKey, LanguageEnum::COPROLALIA_PARAMETERS, true)) {
            return $additionalInfoKey;
        }

        return $parameterKey . '_' . $additionalInfoKey;
    }

    private function getParameterTranslationId(
        string $parameterKey,
        string $additionalInfoKey,
        string $parameterTranslationId
    ): string {
        if (\in_array($parameterKey, LanguageEnum::COPROLALIA_PARAMETERS, true)) {
            return $additionalInfoKey;
        }
        if ($this->shouldReturnRawCharacterKey($parameterKey, $parameterTranslationId)) {
            return $parameterTranslationId;
        }

        return $parameterTranslationId . '.' . $additionalInfoKey;
    }

    private function shouldReturnRawCharacterKey(string $parameterKey, string $parameterTranslationId): bool
    {
        return str_contains($parameterKey, LanguageEnum::CHARACTER) && CharacterEnum::doesNotExist($parameterTranslationId);
    }
}
