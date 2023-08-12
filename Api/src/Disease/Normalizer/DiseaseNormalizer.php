<?php

namespace Mush\Disease\Normalizer;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DiseaseNormalizer implements ContextAwareNormalizerInterface
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
        $description = $this->getSymptomEffects($diseaseConfig, $description, $language);

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

    private function getSymptomEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        // first get symptom effects
        $symptomEffects = [];
        /** @var SymptomConfig $symptomConfig */
        foreach ($diseaseConfig->getSymptomConfigs() as $symptomConfig) {
            $name = $symptomConfig->getSymptomName();

            $randomActivationRequirement = $symptomConfig->getSymptomActivationRequirements()
                ->filter(fn (SymptomActivationRequirement $activationRequirement) => $activationRequirement->getActivationRequirementName() === SymptomActivationRequirementEnum::RANDOM);
            if (!$randomActivationRequirement->isEmpty()) {
                $chance = $randomActivationRequirement->first()->getValue();
            } else {
                $chance = 100;
            }

            $effect = $this->translationService->translate(
                $name . '.description',
                ['chance' => $chance],
                'modifiers',
                $language
            );

            if (!in_array($effect, $symptomEffects)) {
                array_push($symptomEffects, $effect);
            }
        }

        // then add them to the description
        if (!empty($symptomEffects)) {
            foreach ($symptomEffects as $symptomEffect) {
                $description = $description . '//' . $symptomEffect;
            }
        }

        return $description;
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

            if ($effect) {
                if (!in_array($effect, $effects)) {
                    array_push($effects, $effect);
                }
            }
        }

        if (!empty($effects)) {
            foreach ($effects as $effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }
}
