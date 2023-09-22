<?php

namespace Mush\Disease\Normalizer;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DiseaseNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PlayerDisease;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var DiseaseConfig $diseaseConfig */
        $diseaseConfig = $object->getDiseaseConfig();

        $language = $currentPlayer->getDaedalus()->getLanguage();

        $description = $this->translationService->translate(
            "{$diseaseConfig->getDiseaseName()}.description",
            [],
            'disease',
            $language
        );

        $description = $this->getModifierEffects($diseaseConfig, $description, $language);

        return [
            'key' => $diseaseConfig->getDiseaseName(),
            'name' => $this->translationService->translate(
                $diseaseConfig->getDiseaseName() . '.name',
                [],
                'disease',
                $language
            ),
            'type' => $diseaseConfig->getType(),
            'description' => $description,
       ];
    }

    private function getModifierEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        $effects = [];

        /** @var VariableEventModifierConfig $modifierConfig */
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $key = $modifierConfig->getTranslationKey();
            $parameters = $modifierConfig->getTranslationParameters();

            $effect = $this->translationService->translate(
                $key . '.description',
                $parameters,
                'modifiers',
                $language
            );

            if (!in_array($effect, $effects)) {
                $effects[] = $effect;
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }
}
